<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Exception;
use FilesystemIterator;
use LogicException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;
use function Chevereto\Legacy\G\change_pathname_extension;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\get_mimetype;
use function Chevereto\Legacy\G\logger;
use function Chevereto\Legacy\G\random_string;
use function Chevereto\Legacy\G\sanitize_path_slashes;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\str_replace_first;
use function Chevereto\Legacy\G\str_replace_last;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\G\writeToStderr;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\isSafeToExecute;
use function Chevereto\Vars\env;

/**
 * @2024-11-01 tested with 2 threads
 */
class Import
{
    public const DEBUG = 1;

    public const PATH = PATH_APP_LEGACY . 'importer/';

    public const PATH_JOBS = self::PATH . 'jobs/';

    public const CHUNK_SIZE = 500;

    public const METADATA_KEY_TYPES = [
        'album' => 'albumData',
        'user' => 'userData',
        'image' => 'imageData',
    ];

    public string $path;

    public int $id;

    public int $thread;

    /**
     * [root => users] Parse root folders as /user/album?/file.jpg
     * [root => albums] Parse root folders as /album/file.jpg
     * [root => plain] Don't parse folders /file.jpg
     * @var string[]
     */
    public array $options = [
        'root' => 'users',
    ];

    public array $parsedImport = [];

    public array $metadata = [];

    public array $parsed = [];

    public ?string $parse = null;

    public ?string $parseGroup = null;

    protected static array $imageExtensions;

    protected static string $imageExtensionsRegex;

    protected static int $max_execution_time;

    protected string $logFile;

    protected array $import;

    protected $increment;

    protected array $components;

    public function __construct() // $path, $thread=1
    {
        if (! isset(static::$max_execution_time)) {
            if (PHP_SAPI !== 'cli') {
                try {
                    set_time_limit(60);
                    ini_set('max_execution_time', '60');
                } catch (Throwable) {
                    // Ignore
                }
            }
            static::$max_execution_time = (int) ini_get('max_execution_time');
            static::$imageExtensions = Image::getEnabledImageExtensions();
            static::$imageExtensionsRegex = '/\.(' . implode('|', static::$imageExtensions) . ')$/i';
        }
    }

    public static function refresh(): void
    {
        $db = DB::getInstance();
        $db->query(
            'UPDATE '
            . DB::getTable('imports')
            . ' SET import_status = "working" WHERE import_continuous = 1 AND DATE_ADD(import_time_updated, INTERVAL 1 MINUTE) <= UTC_TIMESTAMP();'
        );
        $db->exec();
    }

    public static function autoJobs(): array
    {
        return DB::get('imports', [
            'continuous' => 1,
            'status' => 'working',
        ], 'AND', [
            'field' => 'time_updated',
            'order' => 'asc',
        ]);
    }

    public static function imageExtensionsRegex(): string
    {
        return static::$imageExtensionsRegex;
    }

    public function get(): array
    {
        $this->import = static::getSingle($this->id);
        if ($this->import === []) {
            throw new Exception('Import ID ' . $this->id . 'not found', 100);
        }
        $this->path = $this->import['path'];
        $this->options = $this->import['options'] ? unserialize($this->import['options']) : null;
        $this->parsedImport = array_merge($this->import, [
            'options' => $this->options,
        ]);

        return $this->parsedImport;
    }

    public function checkPath(): void
    {
        $this->path = sanitize_path_slashes(rtrim($this->path, '/'));
        $rootPath = sanitize_path_slashes(rtrim(PATH_PUBLIC, '/'));
        if (stream_resolve_include_path($this->path) == false) {
            throw new Exception("Target path {$this->path} doesn't exists", 100);
        }
        $message = "Target path {$this->path} can't be used for importing";
        if ($this->path == rtrim(CHV_PATH_IMAGES, '/')) {
            throw new Exception("{$message} (file upload path)", 101);
        }
        if ($this->path == $rootPath) {
            throw new Exception("{$message} (application root path)", 101);
        }
        if (starts_with($this->path, $rootPath)) {
            throw new Exception("{$message} (application folder ancestor)", 103);
        }
        if (starts_with($rootPath . '/importing', $this->path)) {
            throw new Exception("{$message} (automatic importing path)", 104);
        }
    }

    public function delete(): int
    {
        $import = static::getSingle($this->id);
        if ($import['continuous'] === 1) {
            throw new LogicException("Import of type continuous can't be deleted");
        }
        DB::delete('importing', [
            'import_id' => $this->id,
        ]);

        return DB::delete('imports', [
            'id' => $this->id,
        ]);
    }

    public function add(): int
    {
        $this->checkPath();
        if (! (new FilesystemIterator($this->path))->valid()) {
            throw new Exception("{$this->path} is empty", 101);
        }
        if ($get = static::getSingle($this->path, 'path')) {
            throw new Exception('Import ID ' . $get['id'] . ' is blocking the addition of a new importer job under the ' . $this->path . ' path', 102);
        }
        $this->id = DB::insert('imports', [
            'time_created' => datetimegmt(),
            'path' => $this->path,
            'options' => $this->options ? serialize($this->options) : null,
            'status' => 'queued',
        ]);

        return $this->id;
    }

    public static function getSingle(int|string $var, string $by = 'id'): array
    {
        $db = DB::getInstance();
        switch ($by) {
            case 'path':
                $where = "import_path=:var AND import_status NOT IN ('completed', 'canceled')";

                break;
            case 'id':
            default:
                $where = 'import_id=:var';

                break;
        }
        $db->query('SELECT * FROM ' . DB::getTable('imports') . " WHERE {$where} LIMIT 1;");
        $db->bind(':var', $var);
        $import = $db->fetchSingle();

        return is_array($import)
            ? DB::formatRows($import, 'import')
            : [];
    }

    public static function getContinuous(): array
    {
        $all = DB::get('imports', [
            'continuous' => 1,
        ]);
        if ($all === []) {
            return [];
        }
        $format = DB::formatRows($all, 'import');
        foreach ($format as &$v) {
            $v['options'] = $v['options'] ? unserialize($v['options']) : null;
        }

        return $format;
    }

    public static function getOneTime(): array
    {
        $all = DB::get('imports', [
            'continuous' => 0,
        ]);
        if ($all === []) {
            return [];
        }
        $format = DB::formatRows($all, 'import');
        foreach ($format as &$v) {
            $v['options'] = $v['options'] ? unserialize($v['options']) : null;
        }

        return $format;
    }

    public function edit(array $values)
    {
        if ($values['options'] ?? false) {
            $values['options'] = serialize($values['options']);
        }
        $values['time_updated'] = datetimegmt();
        DB::update('imports', $values, [
            'id' => $this->id,
        ]);
    }

    public function reset(): void
    {
        $this->edit([
            'status' => 'working',
            'users' => '0',
            'images' => '0',
            'albums' => '0',
            'errors' => '0',
            'started' => '0',
        ]);
        foreach (['errors', 'process'] as $type) {
            $filename = $this->getLogPath() . $type . '.txt';
            if (! file_exists($filename)) {
                continue;
            }
            if (! unlinkIfExists($filename)) {
                throw new Exception('File ' . $filename . " can't be removed", 100);
            }
        }
        $this->get();
    }

    public function resume(): void
    {
        if (! $this->import['continuous']) {
            throw new Exception('Only continuous importing can be resumed', 100);
        }
        $this->edit([
            'status' => 'working',
        ]);
        $this->get();
    }

    public function logProcess(string $message, bool $logError = false): void
    {
        if ($logError) {
            $this->log($message, 'errors');
        }
        $this->log($message, 'process');
    }

    public function logError(string $message): void
    {
        if ($this->import['errors'] == 0) {
            $this->logProcess('Adding "errors" flag to import row');
            $this->edit([
                'errors' => 1,
            ]);
        }

        $this->log($message, 'errors');
    }

    public function logException(Throwable $e): void
    {
        $this->logError(get_class($e) . ' ' . $e->getMessage() . ' ~ ' . $e->getTraceAsString());
    }

    public function process(): void
    {
        if (in_array($this->import['status'], ['paused', 'canceled', 'completed'])) {
            throw new Exception('Import job ID ' . $this->id . ' is ' . $this->import['status'], 900);
        }
        $values = [];
        $this->metadata = [];
        $this->parsed = [];
        $this->logProcess('Import process started (job ID ' . $this->id . ')');
        $this->logProcess(str_repeat('=', 80));
        if ($this->import['started'] == 0) {
            $values['started'] = 1;
            $this->logProcess('Import row has been updated adding the "started" flag');
        }
        if ($this->import['status'] != 'working') {
            $values['status'] = 'working';
        }
        if ($values) {
            $this->edit($values);
            $this->get();
        }
        $killed = false;
        $i = 0;
        $parsedItems = 0;
        $cwd = null; // Current Working Directory
        $pwd = null; // Previous Working Directory

        /**
         * @var \SplFileInfo $fileinfo
         */
        try {
            $getItems = $this->getItems();
        } catch (Throwable $e) {
            $getItems = [];
            $this->logProcess('Unable to get items: ' . $e->getMessage(), true);
            $this->logException($e);
            $killed = true;
        }
        foreach ($getItems as $fileinfo) {
            if (in_array($this->import['status'], ['queued', 'working']) === false) {
                throw new Exception('Import job ID ' . $this->id . ' is ' . $this->import['status'], 900);
            }
            if ($i > 0) {
                $this->logProcess(str_repeat('-', 80));
                // Refresh $import on each loop, needed for hot editing
                $this->get();
            }
            if ($parsedItems > static::CHUNK_SIZE - 1 || isSafeToExecute(static::$max_execution_time) == false) {
                $abortMessage = ($parsedItems > static::CHUNK_SIZE - 1) ? 'Chunk limit reached (' . static::CHUNK_SIZE . ')' : 'About to run out of time';
                $this->logProcess("{$abortMessage}, breaking iteration now");
                $killed = true;

                break;
            }
            $pathHandle = null;
            $insertId = null;
            $parsed = false;
            $i++;
            $this->setParse(null);
            $pathName = $fileinfo->getPathName();
            $this->logProcess("Current iteration: {$pathName}");
            if (! file_exists($pathName)) {
                $this->logProcess('PathName is gone, continue iteration');

                continue;
            }
            if ($fileinfo->isFile()) {
                // File already locked
                if ($lock = $this->getImportingLock($pathName)) {
                    $this->logProcess("Concurrency: {$pathName} is locked by another process, continue iteration");
                    $killed = true;

                    break;
                }
                if ($fileinfo->isWritable()) {
                    // Insert DB lock
                    try {
                        DB::insert('importing', [
                            'import_id' => $this->id,
                            'path' => $pathName,
                            'content_type' => 'image',
                            'content_id' => null,
                        ]);
                    } catch (Exception $e) {
                        $this->logProcess("Unable to insert DB lock for {$pathName}: " . $e->getMessage() . ', breaking iteration');
                        // $this->logException($e);
                        $killed = true;

                        break;
                    }
                }
            }
            // @phpstan-ignore-next-line
            if (! file_exists($pathName)) {
                $this->logProcess('PathName is gone!, continue iteration');

                continue;
            }
            if (! $fileinfo->isWritable()) {
                $this->logProcess("Path {$pathName} is not writable, the job #" . $this->id . ' must be canceled', true);
                $this->edit([
                    'status' => 'canceled',
                    'errors' => '1',
                ]);
                $this->logProcess('Updating importing status to canceled (the error must be addressed)');
                $killed = true;

                break;
            }
            $component = $this->getComponent((string) $fileinfo);
            $this->parseComponent($component);
            if ($this->parse == null) {
                $this->logProcess('No parse applicable, continue iteration');

                continue;
            }
            // For images, we remove the file.ext part
            if ($fileinfo->isFile() && $this->components !== []) {
                array_pop($this->components);
            }
            // Analyze $cwd (at this point, containing previous scanned dir)
            if ($cwd !== null) {
                $pwd = $cwd;
                $this->logProcess("Previous working directory is: {$pwd}");
            }
            if ($fileinfo->isDir()) {
                $cwd = $pathName;
            } else {
                $cwd = $fileinfo->getPath(); // no filename
            }
            $this->logProcess("Current working directory is: {$cwd}");
            /**
             * On directory change, check and delete the already parsed directories
             */
            if ($pwd && $pwd != $cwd) {
                $this->logProcess('Directory changed, about to detect if the previous directory should be removed or not');
                $delete_dir = null;
                // Detect kind of jump
                $pwd_explode = explode('/', ltrim($pwd, '/'));
                $cwd_explode = explode('/', ltrim($cwd, '/'));
                $cnt_pwd = count($pwd_explode);
                $cnt_cwd = count($cwd_explode);
                switch (true) {
                    case $cnt_pwd > $cnt_cwd:
                        $delete_dir = $pwd;
                        $this->logProcess("{$delete_dir} should be removed");

                        break;
                    case $cnt_pwd < $cnt_cwd:
                        $this->logProcess('Entering sub-directory, nothing to remove yet');

                        break;
                    case $cnt_pwd == $cnt_cwd && $pwd != $cwd:
                        $this->logProcess("Entering sibling directory, {$pwd} should be removed");
                        $delete_dir = $pwd;

                        break;
                }
                if ($delete_dir) {
                    $this->removeDir($delete_dir);
                }
            }
            if ($this->options['root'] == 'plain') {
                $pathHandle = null;
            } else {
                $pathHandle = rtrim($this->path, '/') . '/'; // The actual path used for lock, relative to importing path
                if (strpos($component, '/') !== false) { // /some/dir/
                    $pathHandle .= implode('/', $this->components);
                } else { // No sub-dirs here, just files in /
                    $this->logProcess('Plain directory structure detected in component');
                    if ($fileinfo->isFile()) {
                        $pathHandle = null; // file.ext -> null
                    }
                    // Why this??
                    if ($fileinfo->isDir()) {
                        $pathHandle .= $component; // /dir
                    }
                }
                $this->logProcess('Path handle is: ' . ($pathHandle ?: 'null'));
            }
            /**
             * If we are handling a folder, check for any locks preventing dir parsing
             */
            if ($pathHandle) {
                if ($lock = $this->getImportingLock($pathHandle)) {
                    $this->logProcess("Path handle {$pathHandle} is already locked in DB");
                    /**
                     * No content id: The lock is being created. Terminate.
                     */
                    if ($lock['content_id'] === null) {
                        $this->logProcess('Content id has not been set, another process is working in this same path, KILL operation');
                        $killed = true;

                        break;
                    }
                    /**
                     * Content id: This folder has been parsed. Get the content
                     * id + type associated to this dir
                     */
                    $content_id = $lock['content_id'];
                    $content_type = $lock['content_type'];
                    $this->logProcess("Content ID ({$content_type}): {$content_id} (taken from DB lock)");
                } else {
                    /**
                     * Note: No image should be here anyway...
                     */
                    if ($this->parse == 'image') {
                        $this->logProcess("This shouldn't be logged!!!!! PANIC!");

                        break;
                    }

                    /**
                     * Try to create the lock AND parse path contents
                     */
                    try {
                        $this->logProcess("Path handle {$pathHandle} is not locked, about to create DB lock for it");
                        // Insert DB lock
                        $lockId = DB::insert('importing', [
                            'import_id' => $this->id,
                            'path' => $pathHandle,
                            'content_type' => $this->parse,
                            'content_id' => null, // dummy
                        ]);
                        $this->logProcess('DB lock inserted (' . $lockId . '), about to parse directory as ' . $this->parse);
                        $this->parseMetadata($cwd . '/metadata.json');
                        // TODO: Always parse metadata updates (if needed)
                        // Switch depending on dir kind
                        switch ($this->parse) {
                            case 'user':
                                // By default we look for matching users...
                                $userLookup = true;
                                $username = basename($pathHandle);
                                $usernameMaxLength = (int) Settings::USERNAME_MAX_LENGTH;
                                $usernameMinLength = (int) Settings::USERNAME_MIN_LENGTH;
                                // Replace spaces
                                $usernameClean = preg_replace('/\s+/', '_', $username);
                                // Get only \w
                                $usernameClean = preg_replace('/\W/', '', $usernameClean);
                                // Make sure to fulfill the limit
                                $usernameClean = substr($usernameClean, 0, $usernameMaxLength);
                                // Add some padding
                                $usernameCleanLen = strlen($usernameClean);
                                if ($usernameCleanLen < $usernameMinLength) {
                                    $usernameClean .= '_' . random_string($usernameMinLength - $usernameCleanLen);
                                }
                                // Folder name doesn't satisfy a valid username string
                                if ($username != $usernameClean) {
                                    $this->logProcess("Username {$username} is invalid username string, switching to {$usernameClean}");
                                    // Don't look, just create a new user
                                    $userLookup = false;
                                }
                                $parsed = array_merge([
                                    'username' => $username,
                                    'registration_ip' => '127.0.0.1',
                                ], $this->parsed);
                                // If username exists, assign its $content_id
                                $user = [];
                                if ($userLookup) {
                                    $user = User::getSingle($username, 'username');
                                }
                                if ($user !== []) {
                                    $this->logProcess("Username {$username} already exists");
                                    $insertId = $user['id'];
                                    if ($this->parsed !== []) {
                                        $this->logProcess("About to update {$username} ({$insertId}) with parsed data " . json_encode($this->parsed));
                                        User::update($insertId, $this->parsed);
                                        $this->logProcess('Updated parsed user metadata');
                                    }
                                } else {
                                    // Make sure to insert a new user
                                    $u = 0;
                                    while (User::getSingle($usernameClean, 'username') !== []) {
                                        $this->logProcess("Must try a different username as {$usernameClean} already exists");
                                        // It strips the number previously appended, so we get user1, user2, and so on.
                                        if ($u > 0) {
                                            $usernameClean = str_replace_last((string) $u, '', $usernameClean);
                                        }
                                        // Soon as this gets too big, we trim the last $usernameClean char
                                        if (strlen($usernameClean . $u) > $usernameMaxLength) {
                                            $usernameClean = substr($usernameClean, 0, -1);
                                        }
                                        $u++;
                                        $usernameClean .= $u;
                                        $parsed['username'] = $usernameClean;
                                    }
                                    $this->logProcess("About to insert user {$usernameClean}");
                                    $insertId = User::insert($parsed);
                                    $this->logProcess("Username {$usernameClean} (id {$insertId}) inserted");
                                    $user = User::getSingle($insertId, 'id');
                                }
                                if ($user !== [] && isset($this->metadata['profileImages'])) {
                                    try {
                                        foreach ($this->metadata['profileImages'] as $k => $v) {
                                            $userAsset = [
                                                'name' => 'asset.jpg',
                                                'type' => 'image/jpeg', // dummy
                                                'tmp_name' => $pathName . '/.assets/' . $v,
                                                'error' => 0,
                                                'size' => 1,
                                            ];
                                            $this->logProcess("Uploading user {$k} image");
                                            if (file_exists($userAsset['tmp_name'])) {
                                                try {
                                                    User::uploadPicture($user, $k, $userAsset);
                                                } catch (Exception) {
                                                    $this->logProcess(sprintf('Failed to upload user %s', $k), true);
                                                }
                                                $this->logProcess('Uploaded ' . $userAsset['tmp_name']);
                                            } else {
                                                $this->logProcess(sprintf('Skipping missing asset at %s', $userAsset['tmp_name']));
                                            }
                                        }
                                    } catch (Exception $e) {
                                        $theUser = $k ?? '';
                                        $this->logProcess("Failed to upload user {$theUser}: " . $e->getMessage());
                                        $this->logException($e);
                                    }
                                }

                                break;
                            case 'album':
                                $albumName = basename($pathHandle);
                                $this->logProcess("About to get import lock for album at {$pathHandle}");
                                $import_lock = $this->getImportingLock($pathHandle);
                                $insertId = $import_lock['content_id'];
                                $user_id = null;
                                $parent_id = null;
                                if ($insertId == 0) {
                                    $dirname = dirname($pathHandle);
                                    $this->logProcess("No content found, about to check for a parent lock for {$dirname}");
                                    $parent_lock = $this->getImportingLock($dirname);
                                    if ($parent_lock !== []) {
                                        $this->logProcess('Parent ' . ($parent_lock['content_type'] ?? 'null') . ' id ' . ($parent_lock['content_id'] ?? 'null'));
                                        if (($parent_lock['content_type'] ?? null) === 'user') {
                                            $user_id = $parent_lock['content_id'];
                                        } elseif (isset($parent_lock['content_id'])) {
                                            $user_id = DB::get('albums', [
                                                'id' => $parent_lock['content_id'],
                                            ])[0]['album_user_id'] ?? null;
                                            $this->logProcess(sprintf('User id %s taken from parent context', (string) ($user_id ?? 'null')));
                                            $parent_id = $parent_lock['content_id'];
                                        }
                                    } else {
                                        $this->logProcess('No parent lock');
                                    }
                                    if ($user_id === null && ! getSetting('guest_albums')) {
                                        throw new LogicException('Guest albums are disabled, skipping album ' . $albumName);
                                    }
                                    $parsed = array_merge([
                                        'name' => $albumName,
                                        'user_id' => $user_id,
                                        'privacy' => 'public',
                                        'description' => '',
                                        'password' => null,
                                        'creation_ip' => '127.0.0.1',
                                        'parent_id' => $parent_id,
                                    ], $this->parsed);
                                    $this->logProcess('About to insert album "' . $parsed['name'] . '" under user_id ' . ($parsed['user_id'] ?: 'guest') . ' and parent album id ' . $parsed['parent_id']);
                                    $insertId = Album::insert($parsed);
                                    if (isset($user_id)) {
                                        DB::increment('users', [
                                            'album_count' => '+1',
                                        ], [
                                            'id' => $user_id,
                                        ]);
                                    }
                                    $this->logProcess("Album {$albumName} inserted (id {$insertId})");
                                } else {
                                    $this->logProcess("Album {$albumName} already exists (id {$insertId})");
                                }

                                break;
                        }
                        // Update lock content_id
                        $this->logProcess('About to update importing table (current job)');
                        DB::update('importing', [
                            'content_id' => $insertId,
                        ], [
                            'id' => $lockId,
                        ]);
                        $this->logProcess('Importing table updated');
                    } catch (Exception $e) {
                        $this->logProcess("Process interrupted when parsing {$pathHandle}, check the error log");
                        $this->logException($e);
                        if (isset($lockId)) {
                            $this->logProcess("Unable to parse directory, about to release DB lock ({$lockId})");
                            DB::delete('importing', [
                                'id' => $lockId,
                            ]);
                            $this->logProcess("DB lock ({$lockId}) released");
                        } else {
                            $this->logProcess("Unable to insert DB lock for {$pathHandle}: " . $e->getMessage() . ', continue iteration');
                        }

                        continue;
                    }
                }
            }
            if ($this->parse == 'image') {
                $this->logProcess('About to parse image: ' . $pathName);
                $mimetype = get_mimetype($pathName);
                $parsed = [
                    'name' => $fileinfo->getFilename(),
                    'type' => $mimetype,
                    'tmp_name' => $pathName,
                    'error' => 0,
                    'size' => 1, // $fileinfo->getSize() sometimes fails...
                ];
                $user_id = null;
                $params = [];
                if ($pathHandle && isset($content_type, $content_id)) {
                    $content_id = (int) $content_id;
                    $this->logProcess('Using DB lock for content context data (id and type)');
                    if ($content_type == 'user') {
                        $user_id = $content_id;
                    } else {
                        $params['album_id'] = $content_id;
                        $album = Album::getSingle(id: $content_id, pretty: false);
                        if (isset($album['user_id'])) {
                            $user_id = $album['user_id'];
                        }
                    }
                }

                try {
                    $params['use_file_date'] = true;
                    // @deprecate $params['mimetype']
                    $params['mimetype'] = $mimetype;
                    $user = User::getSingle($user_id, 'id');
                    $metaFile = $pathName . '.json';
                    if (! file_exists($metaFile)) {
                        $metaFile = change_pathname_extension($pathName, 'json');
                    }
                    $this->parseMetadata($metaFile);
                    if (! isset($this->parsed['category_id'])) {
                        $this->logProcess('No implicit categoryId property found, about to check category metadata object');
                        $urlKey = isset($this->metadata['category']['urlKey']) ? $this->metadata['category']['urlKey'] : null;
                        if (isset($urlKey)) {
                            $this->logProcess("Explicit urlKey property declared, determine its category ID (create if doesn't exists)");
                            $category = DB::get('categories', [
                                'url_key' => $this->metadata['category']['urlKey'],
                            ])[0] ?? null;
                            if (isset($category)) {
                                $categoryId = $category['category_id'];
                                $this->logProcess(_s("%s ID set: {$categoryId}", _s('Category')));
                            } else {
                                $category = [
                                    'url_key' => $urlKey,
                                    'name' => isset($this->metadata['category']['name']) ? $this->metadata['category']['name'] : $urlKey,
                                    'description' => isset($this->metadata['category']['description']) ? $this->metadata['category']['description'] : '',
                                ];

                                try {
                                    $categoryId = DB::insert('categories', $category);
                                    $this->logProcess(_s("%s ID: {$categoryId} created", _s('Category')));
                                } catch (Exception $e) {
                                    $this->logProcess("Unable to create category {$urlKey}: " . $e->getMessage(), true);
                                    $this->logException($e);
                                }
                            }
                            if (isset($categoryId)) {
                                $this->parsed['category_id'] = $categoryId;
                            }
                        }
                    }
                    if ($this->parsed !== []) {
                        $this->logProcess('Image using parsed metadata ' . json_encode($this->parsed));
                        $params = array_merge($params, $this->parsed);
                    }
                    $uploadToWebsite = Image::uploadToWebsite($parsed, $user, $params, false, '127.0.0.1');
                    $insertId = $uploadToWebsite[0];
                    $this->logProcess("Image ID {$insertId} inserted");
                } catch (Exception $e) {
                    if ($e->getCode() == 666) {
                        $this->logProcess($e->getMessage());
                    } else {
                        $basename = basename($this->path);
                        $dirname = dirname($this->path);
                        $failedPath = $dirname . '/failed/' . time() . '/' . $basename;
                        $this->logProcess('Failed to insert image, exception thrown: ' . $e->getMessage());
                        $this->logError("Image insertion failed for {$pathName}");
                        $this->logException($e);
                        $failedPathName = str_replace_first($this->path, $failedPath, $pathName);
                        $failedPathDirname = dirname($failedPathName);
                        if (! is_dir($failedPathDirname)) {
                            mkdir($failedPathDirname, 0755, true);
                            $this->logProcess("* Made directory {$failedPath}");
                        }

                        try {
                            $renamed_import = rename($pathName, $failedPathName);
                        } catch (Throwable) {
                            $renamed_import = file_exists($failedPathName);
                        }
                        if (! $renamed_import) {
                            $this->logProcess("! Failed to remove {$pathName} from importing path", true);
                        } else {
                            $this->logProcess("* Image {$pathName} moved to {$failedPathName}", true);
                        }
                    }
                }
            }
            if ($insertId) {
                $this->logProcess('Inserted content, items++');
                DB::increment(
                    'imports',
                    [
                        $this->parseGroup => '+1',
                    ],
                    [
                        'id' => $this->id,
                    ]
                );
                $this->edit([]); // updates timestamp
                $parsedItems++;
            }
        }
        // Nothing left to parse, complete the process and wipe the path
        if ($killed == false && ($i == 0 || $parsedItems == 0)) {
            $this->logProcess('Nothing parsed in ' . $this->path);

            try {
                $this->edit([
                    'status' => 'completed',
                ]);
                DB::delete('importing', [
                    'import_id' => $this->id,
                ]);
                $this->logProcess('DB status changed to completed');
                if ($this->import['continuous']) {
                    $this->logProcess('DB status should be changed to "working" to keep this job alive');
                }
            } catch (Exception $e) {
                $this->logProcess('Error updating DB: ' . $e->getMessage(), true);
                $this->logException($e);
            }
            if ($this->import['continuous']) {
                if ($this->removeDir($this->path, false) == false) {
                    $this->logProcess('Unable to remove ' . $this->path . ' contents', true);
                }
            } else {
                if ($this->removeDir($this->path) == false) {
                    $this->logProcess('Unable to remove ' . $this->path, true);
                }
            }
        }
        $this->logProcess('Chunked process ended' . ($killed ? ' (killed)' : null));
        $this->logProcess(str_repeat('=', 80));
    }

    public function getItems()
    {
        if (! is_dir($this->path)) {
            return [];
        }
        $iterator = new RecursiveDirectoryIterator($this->path);
        $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

        return new ImporterFilterIterator(new RecursiveIteratorIterator($iterator, RecursiveIteratorIterator::SELF_FIRST));
    }

    public function isLocked(): bool
    {
        $path = $this->path . '/.lock';
        clearstatcache(true, $path);

        return file_exists($path);
    }

    public function getComponent(string $filepath): string
    {
        $component = str_replace_first($this->path, '', (string) $filepath);
        $return = ltrim(rtrim($component, '/'), '/');
        $this->logProcess("Component is: {$return}");

        return $return;
    }

    /**
     * @param string $component Path section (without the $importer path)
     */
    public function parseComponent(string $component): void
    {
        $this->logProcess("About to parse component for {$component} (root: " . $this->options['root'] . ')');
        $this->components = explode('/', $component); // /0/1/2/3/n...
        $this->setParse(null);
        if (preg_match(static::$imageExtensionsRegex, $component) == true) {
            $this->setParse('image');

            return;
        }
        $component_cnt = count($this->components);
        switch ($this->options['root']) {
            case 'users':
                if ($component_cnt === 1) {
                    $this->setParse('user');
                }
                if ($component_cnt > 1) {
                    $this->setParse('album');
                }

                break;
            case 'albums':
                $this->setParse('album');

                break;
        }
        if ($this->parse == null) {
            $this->logProcess('Parse is null');
        }
    }

    public function parseMetadata(string $filename, ?string $type = null): void
    {
        $this->metadata = [];
        $this->parsed = [];
        if (stream_resolve_include_path($filename) == false) {
            return;
        }
        if ($type == null) {
            $type = $this->parse;
        }
        if (array_key_exists($type, static::METADATA_KEY_TYPES) == false) {
            $this->logProcess("Error: Invalid type {$type} metadata key", true);

            return;
        }
        $metadataKey = static::METADATA_KEY_TYPES[$type];
        if (is_readable($filename) == false) {
            $this->logProcess("File reading error: {$filename} is not readable", true);

            return;
        }
        if ($contents = @file_get_contents($filename)) {
            $this->logProcess("{$filename} readed");
            $metadata = json_decode($contents, true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                $this->logProcess("File format error: {$filename} contains invalid JSON", true);
                $metadata = null;
            }
        } else {
            $this->logProcess("Unable to read {$filename}", true);

            return;
        }
        if ($metadata = $metadata[$metadataKey]) {
            // [0 => 'TYPE', keys] Wow, such typing. Very modern.
            switch ($type) {
                case 'album':
                    $tr = [
                        'name' => [
                            0 => 'string',
                            'title',
                        ],
                        'description' => [
                            0 => 'string',
                            'description',
                        ],
                        'privacy' => [
                            0 => 'string',
                            ['privacy', 'type'],
                        ],
                        'password' => [
                            0 => 'string',
                            ['privacy', 'password'],
                        ],
                    ];

                    break;
                case 'user':
                    $tr = [
                        'name' => [
                            0 => 'string',
                            'name',
                        ],
                        'email' => [
                            0 => 'string',
                            'email',
                        ],
                        'website' => [
                            0 => 'string',
                            'website',
                        ],
                        'bio' => [
                            0 => 'string',
                            'bio',
                        ],
                        'facebook_username' => [
                            0 => 'string',
                            ['networks', 'facebook'],
                        ],
                        'twitter_username' => [
                            0 => 'string',
                            ['networks', 'twitter'],
                        ],
                        'timezone' => [
                            0 => 'string',
                            'timezone',
                        ],
                        'language' => [
                            0 => 'string',
                            'language',
                        ],
                        'is_private' => [
                            0 => 'boolean',
                            'is_private',
                        ],
                        'is_manager' => [
                            0 => 'boolean',
                            'is_manager',
                        ],
                        'is_admin' => [
                            0 => 'boolean',
                            'is_admin',
                        ],
                    ];

                    break;
                case 'image':
                    $tr = [
                        'title' => [
                            0 => 'string',
                            'title',
                        ],
                        'tags' => [
                            0 => 'string',
                            'tags',
                        ],
                        'description' => [
                            0 => 'string',
                            'description',
                        ],
                        'category_id' => [
                            0 => 'integer',
                            'categoryId',
                        ],
                        'nsfw' => [
                            0 => 'boolean',
                            'nsfw',
                        ],
                    ];

                    break;
            }
            $parsed = [];
            // date->timestamp must be handled as date + date_gmt
            // Assign the parse props based on the $tr array
            foreach ($tr ?? [] as $metaProp => $metaValue) {
                $propValue = null;
                $propType = $metaValue[0];
                $val = $metaValue[1];
                if (is_array($val)) {
                    $propValue = $metadata[$val[0]] ?? null;
                } else {
                    $propValue = $metadata[$val] ?? null;
                }
                $propValue = $metadata[is_array($val) ? $val[0] : $val]
                    ?? null;
                if (isset($propValue)) {
                    if (is_array($val) && is_array($propValue)) {
                        unset($val[0]); // Get rid of the parent (already taken just above)
                        foreach ($val as $v) {
                            if (($propValue[$v] ?? false) === false) {
                                break;
                            }
                            $propValue = $propValue[$v];
                        }
                    }
                }
                if ($propValue) {
                    if ($metaProp === 'tags' && is_array($propValue)) {
                        $propValue = implode(',', $propValue);
                    }
                    $gettype = gettype($propValue);
                    if ($gettype != $propType) {
                        $this->logProcess("Metadata error: Type {$gettype} provided, expected {$propType} for {$metaProp}");

                        continue;
                    }
                    $parsed[$metaProp] = $propValue;
                }
            }
            $this->metadata = $metadata;
            $this->parsed = $parsed;
        } else {
            $this->logProcess("Metadata error: Missing metakey {$metadataKey}");
        }
    }

    protected function getImportingLock(string $pathName): array
    {
        $this->logProcess("About to get DB importing lock for {$pathName}");
        $importing = DB::get('importing', [
            'path' => $pathName,
        ])[0] ?? null;
        if (isset($importing)) {
            return DB::formatRows($importing, 'importing');
        }

        return [];
    }

    /**
     * Logger helper
     * Writes logs in importer/jobs/<id> with filenames like error.2.txt for
     * errors being catch by the thread id "2"
     */
    protected function log(string $message, string $type): bool
    {
        $logPath = $this->getLogPath();
        if (stream_resolve_include_path($logPath) == false) {
            mkdir($logPath, 0755, true);
        }
        // $logFile = $logPath . $type . '.' . $this->thread . '.txt';
        $logFile = $logPath . $type . '.txt';
        $message = time() . ' - ' . '[Thread #' . $this->thread . '] ' . $message . "\n";
        logger($message);
        $fpc = file_put_contents($logFile, $message . "\n", FILE_APPEND);
        if ((env()['CHEVERETO_SERVICING'] ?? null) === 'docker' && $type === 'errors') {
            writeToStderr($message);
        }

        return $fpc !== false;
    }

    protected function setParse(?string $parse = null): void
    {
        $this->parse = $parse;
        $this->parseGroup = $parse == null
            ? null
            : ($this->parse . 's');
        if ($parse !== null) {
            $this->logProcess("Parse has been set to: {$parse}");
        }
    }

    /**
     * @param string $dir Directory to wipe
     * @return array|bool TRUE if the directory was wiped *or empty. Array of items
     * failed to delete
     */
    protected function removeDir(string $dir, bool $removeSelf = true): array|bool
    {
        $contents = ! $removeSelf ? ' contents' : '';
        $failed = [];
        $this->logProcess("About to remove {$dir} directory{$contents} (recursively)...");
        if (! is_dir($dir)) {
            $this->logProcess("The directory doesn't exists, no need to remove it");

            return true;
        }
        $isDirEmpty = ! (new FilesystemIterator($dir))->valid();
        if ($isDirEmpty) {
            $this->logProcess('The directory is already empty, no need to iterate its contents');
            if ($removeSelf) {
                $res = rmdir($dir);
            } else {
                $res = true;
            }
        } else {
            $this->logProcess('The directory is not empty, prepare to iterate and remove its contents');
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            foreach ($files as $fileinfo) {
                $filepath = $fileinfo->getRealPath();
                $todo = $fileinfo->isDir() ? 'rmdir' : 'unlink';
                $type = $fileinfo->isDir() ? ('directory' . $contents) : 'file';
                $this->logProcess("Loop: Removing {$type} {$filepath} ({$todo})");
                $res = @$todo($filepath);
                if ($res == false) {
                    $this->logProcess("Unable to remove {$filepath}", true);
                    $failed[] = [
                        'filepath' => $filepath,
                        'isDir' => $fileinfo->isDir(),
                    ];
                }
            }
            if ($removeSelf) {
                $res = rmdir($dir);
            } else {
                $res = true;
            }
        }
        if ($res == true) {
            $this->logProcess("Directory {$dir}{$contents} removed");

            return true;
        }

        return $failed;
    }

    private function getLogPath(): string
    {
        return static::PATH_JOBS . $this->id . '/';
    }
}
