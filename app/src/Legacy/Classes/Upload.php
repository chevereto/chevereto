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

use Chevereto\Config\Config;
use Exception;
use Intervention\Image\ImageManagerStatic;
use LogicException;
use PHPExif\Exif;
use Throwable;
use function Chevere\Message\message;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\ends_with;
use function Chevereto\Legacy\G\fetch_url;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\forward_slash;
use function Chevereto\Legacy\G\get_basename_without_extension;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_file_extension;
use function Chevereto\Legacy\G\get_filename;
use function Chevereto\Legacy\G\get_image_fileinfo;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\get_video_fileinfo;
use function Chevereto\Legacy\G\is_animated_webp;
use function Chevereto\Legacy\G\is_image_url;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\is_writable;
use function Chevereto\Legacy\G\name_unique_file;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\missing_values_to_exception;
use function Chevereto\Legacy\system_notification_email;
use function Chevereto\Vars\env;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;

class Upload
{
    public const URL_SCHEMES = [
        'http',
        'https',
        'ftp',
    ];

    public bool $detectFlood = true;

    public string $mediaType = 'image';

    private string $source_name;

    private string $extension;

    private array $source_image_fileinfo;

    private string $fixed_filename;

    private ?Exif $source_image_exif = null;

    private string $uploaded_file;

    private ImageConvert $ImageConvert;

    private ?object $moderation = null;

    // filename => name.ext
    // file => /full/path/to/name.ext
    // name => name

    private array|string $source;

    private string $source_extension;

    private array $uploaded = [];

    private array $options = [];

    private string $destination;

    private string $type;

    private string $name;

    private ?int $storage_id;

    private string $downstream;

    private string $source_filename;

    public function uploaded(): array
    {
        return $this->uploaded;
    }

    public function source(): string|array
    {
        return $this->source;
    }

    public function moderation(): ?object
    {
        return $this->moderation;
    }

    public function checkValidUrl(string $url): void
    {
        $aux = strtolower($url);
        $scheme = parse_url($aux, PHP_URL_SCHEME);
        if (! in_array($scheme, self::URL_SCHEMES, true)) {
            throw new LogicException(
                message(
                    'Unsupported URL scheme `%scheme%`',
                    scheme: $scheme
                ),
                400
            );
        }
        $host = parse_url($aux, PHP_URL_HOST);
        if (parse_url(Config::host()->hostname(), PHP_URL_HOST) === $host) {
            throw new LogicException(
                message('Unsupported self host URL upload'),
                400
            );
        }
        $ip = gethostbyname($host);
        $typePub = \IPLib\Range\Type::getName(\IPLib\Range\Type::T_PUBLIC);
        $address = \IPLib\Factory::parseAddressString($ip);
        $type = $address->getRangeType();
        $typeName = \IPLib\Range\Type::getName($type);
        if ($typeName !== $typePub) {
            throw new LogicException(
                message('Unsupported non-public IP address for upload'),
                400
            );
        }
    }

    public function setSource(array|string $source): void
    {
        $this->source = $source;
        $this->type = (is_image_url($this->source) || is_url($this->source))
            ? 'url'
            : 'file';
        $this->source_extension = pathinfo(
            $this->type === 'url'
                ? $this->source
                : $this->source['name'],
            PATHINFO_EXTENSION
        );
        $this->source_extension = strtolower($this->source_extension);
        if ($this->type === 'url') {
            if (Settings::get('enable_uploads_url') === false) {
                throw new LogicException(
                    message('URL uploading is disabled'),
                    403
                );
            }
            $this->checkValidUrl($this->source);
        }
    }

    public function setDestination(string $destination): void
    {
        $this->destination = forward_slash($destination);
    }

    public function setStorageId(?int $storage_id): void
    {
        $this->storage_id = $storage_id;
    }

    public function setFilename(string $name): void
    {
        $this->name = $name;
    }

    public function setOptions(array $options): void
    {
        $this->options = $options;
    }

    public function setOption(string $key, mixed $value): void
    {
        $this->options[$key] = $value;
    }

    public static function getDefaultOptions(): array
    {
        return [
            'max_size' => get_bytes('2 MB'),
            'filenaming' => 'original',
            'exif' => true,
            'allowed_formats' => self::getAvailableImageFormats(),
        ];
    }

    public function exec(): void
    {
        $this->options = array_merge(self::getDefaultOptions(), (array) $this->options);
        $this->validateInput(); // Exception 1
        $this->fetchSource(); // Exception 2
        $this->validateSourceFile(); // Exception 3
        if (! is_array($this->options['allowed_formats'])) {
            $this->options['allowed_formats'] = explode(',', $this->options['allowed_formats']);
        }
        $this->source_name = get_basename_without_extension($this->type === 'url' ? $this->source : $this->source['name']);
        $this->extension = $this->source_image_fileinfo['extension'];
        if ($this->extension === 'jpeg' && $this->source_extension === 'jpg') {
            $this->extension = 'jpg';
        }
        if (! isset($this->name)) {
            $this->name = $this->source_name;
        }
        $this->name = ltrim($this->name, '.');
        if (get_file_extension($this->name) === $this->extension) {
            $this->name = get_basename_without_extension($this->name);
        }
        $this->fixed_filename = preg_replace('/(.*)\.(th|md|original|lg)\.([\w]+)$/', '$1.$3', $this->name . '.' . $this->extension);
        $is_360 = false;
        if (in_array($this->extension, ['jpg', 'jpeg'], true)) {
            $xmpDataExtractor = new XmpMetadataExtractor();
            $xmpData = $xmpDataExtractor->extractFromFile($this->downstream);
            $reader = \PHPExif\Reader\Reader::factory(\PHPExif\Reader\Reader::TYPE_NATIVE);
            $is_360 = false;
            if (isset($xmpData['rdf:RDF']['rdf:Description']['@attributes']['ProjectionType'])) {
                $is_360 = $xmpData['rdf:RDF']['rdf:Description']['@attributes']['ProjectionType'] === 'equirectangular';
            }
            if (array_key_exists('exif', $this->options)) {
                try {
                    $this->source_image_exif = $reader->read($this->downstream);
                } catch (Throwable) {
                }
                if ($this->source_image_exif instanceof Exif) {
                    $this->source_image_exif->setFileName($this->source_filename);
                    $orientation = $this->source_image_exif->getOrientation();
                    // Note: Zero string means false in this context
                    if ($orientation === '0') {
                        $orientation = false;
                    }
                    if ($orientation !== false) {
                        ImageManagerStatic::make($this->downstream)->orientate()->save();
                    }
                }
                if (! $this->options['exif']) {
                    $this->source_image_exif = null;
                    if (ImageManagerStatic::getManager()->config['driver'] === 'imagick') {
                        $img = ImageManagerStatic::make($this->downstream);
                        $img->getCore()->stripImage();
                        $img->save();
                    } else {
                        $img = @imagecreatefromjpeg($this->downstream);
                        if ($img) {
                            imagejpeg($img, $this->downstream, 90);
                            imagedestroy($img);
                        } else {
                            throw new Exception('Unable to create a new JPEG without Exif data', 644);
                        }
                    }
                }
            }
        }
        /*
         * Set uploaded_file
         * Local storage uploads will be allocated at the target destination
         * External storage will be allocated to the temp directory
         */
        if (isset($this->storage_id)) {
            $this->uploaded_file = forward_slash(dirname($this->downstream))
                . '/'
                . Storage::getStorageValidFilename($this->fixed_filename, $this->storage_id, $this->options['filenaming'], $this->destination);
        } else {
            $this->uploaded_file = name_unique_file($this->destination, $this->fixed_filename, $this->options['filenaming']);
        }
        $this->panicExtension($this->uploaded_file);
        $this->source = [
            'filename' => $this->source_filename, // file.ext
            'name' => $this->source_name, // file
            'image_exif' => $this->source_image_exif,
            'type' => $this->mediaType,
            'fileinfo' => $this->source_image_fileinfo,
        ];
        if (stream_resolve_include_path($this->downstream) === false) {
            throw new Exception('Concurrency: Downstream gone, aborting operation', 666);
        }
        if (stream_resolve_include_path($this->uploaded_file) !== false) {
            throw new Exception('Concurrency: Target uploaded file already exists, aborting operation', 666);
        }

        try {
            $uploaded = rename($this->downstream, $this->uploaded_file);
        } catch (Throwable) {
            $uploaded = file_exists($this->uploaded_file);
        }
        unlinkIfExists($this->downstream);
        if (! $uploaded) {
            unlinkIfExists($this->uploaded_file);

            throw new Exception("Can't move temp file to its destination", 600);
        }
        if (! isset($this->storage_id)) {
            try {
                chmod($this->uploaded_file, 0644);
            } catch (Throwable) {
            }
        }
        $fileInfo = $this->mediaType === 'video'
            ? get_video_fileinfo($this->uploaded_file)
            : get_image_fileinfo($this->uploaded_file);
        if ($fileInfo === []) {
            throw new Exception("Can't get uploaded info", 610);
        }
        $fileInfo['is_360'] = $is_360;
        $frameFile = null;
        if ($this->mediaType === 'video') {
            $frameFile = Image::getVideoFrame(
                $this->uploaded_file,
                (int) ($fileInfo['duration'] / 4)
            );
        }
        $this->uploaded = [
            'file' => $this->uploaded_file,
            'filename' => get_filename($this->uploaded_file),
            'name' => get_basename_without_extension($this->uploaded_file),
            'type' => $this->mediaType,
            'fileinfo' => $fileInfo,
            'frame' => $frameFile,
            'frameinfo' => $frameFile ? get_image_fileinfo($frameFile) : [],
            'extension' => $this->extension,
        ];
    }

    public static function getAvailableImageFormats(): array
    {
        return explode(',', Settings::UPLOAD_AVAILABLE_IMAGE_FORMATS);
    }

    public static function getAvailableTypes(): array
    {
        // 0: all
        return [
            'image', // 2^0
            'video', // 2^1
            // 'audio', // 2^2
            // 'document', // 2^3
            // 'other' // 2^4
        ];
    }

    public static function getTempNam(string $failoverDirectory = ''): string
    {
        if ($failoverDirectory === '') {
            $failoverDirectory = sys_get_temp_dir();
        }
        $prefix = env()['CHEVERETO_ID_HANDLE'] . 'chvtemp_';
        $tempNam = @tempnam(sys_get_temp_dir(), $prefix);
        if (! $tempNam || ! @is_writable($tempNam)) {
            $tempNam = @tempnam($failoverDirectory, $prefix);
            if (! $tempNam) {
                throw new Exception("Can't get a tempnam", 600);
            }
        }

        return $tempNam;
    }

    /**
     * validate_input aka "first stage validation"
     * This checks for valid input source data.
     *
     * @Exception 1XX
     */
    protected function validateInput(): void
    {
        $check_missing = ['type', 'source', 'destination'];
        missing_values_to_exception($this, Exception::class, $check_missing, 600);
        if (! preg_match('/^(url|file)$/', $this->type)) {
            throw new Exception('Invalid upload type', 610);
        }
        if ($this->detectFlood) {
            $flood = self::handleFlood();
            if ($flood !== []) {
                throw new Exception(
                    _s(
                        'Flooding detected. You can only upload %limit% %content% per %time%',
                        [
                            '%content%' => _n('image', 'images', $flood['limit']),
                            '%limit%' => $flood['limit'],
                            '%time%' => $flood['by'],
                        ]
                    ),
                    130
                );
            }
        }
        if ($this->type === 'file') {
            if (count($this->source) < 5) { // Valid $_FILES ?
                throw new Exception('Invalid file source', 620);
            }
        } elseif ($this->type === 'url') {
            if (! is_image_url($this->source) && ! is_url($this->source)) {
                throw new Exception('Invalid image URL', 622);
            }
        }
        // Race condition
        if (! is_dir($this->destination)) {
            $base_dir = add_ending_slash(PATH_PUBLIC . explode('/', preg_replace('#' . PATH_PUBLIC . '#', '', $this->destination, 1))[0]);
            $base_perms = fileperms($base_dir);
            $old_umask = umask(0);
            $use_perms = $base_perms === false
                ? 0755
                : $base_perms;

            try {
                $make_destination = mkdir($this->destination, $use_perms, true);
                chmod($this->destination, $base_perms);
                umask($old_umask);
            } catch (Throwable) {
                $make_destination = is_dir($this->destination);
            }
            if (! $make_destination) {
                throw new Exception('Destination ' . $this->destination . ' is not a dir', 630);
            }
        }
        if (! is_readable($this->destination)) {
            throw new Exception("Can't read target destination dir", 631);
        }
        if (! is_writable($this->destination)) {
            throw new Exception("Can't write target destination dir", 632);
        }
        $this->destination = add_ending_slash($this->destination);
    }

    protected function panicExtension(string $filename)
    {
        if (
            ends_with('.php', $filename)
            || ends_with('.htaccess', $filename)) {
            throw new Exception(sprintf('Unwanted extension for %s', $filename), 600);
        }
        $extension = get_file_extension($filename);
        if (! in_array($extension, Image::getEnabledImageExtensions(), true)) {
            throw new Exception(sprintf('Unable to handle upload for %s', $filename), 600);
        }
    }

    protected function fetchSource(): void
    {
        $this->downstream = static::getTempNam($this->destination);
        if ($this->type === 'file') {
            if ($this->source['error'] !== UPLOAD_ERR_OK) {
                switch ($this->source['error']) {
                    case UPLOAD_ERR_INI_SIZE:
                        throw new Exception(
                            'File too big (UPLOAD_ERR_INI_SIZE)',
                            601
                        );
                    case UPLOAD_ERR_FORM_SIZE:
                        throw new Exception(
                            'File exceeds form max size (UPLOAD_ERR_FORM_SIZE)',
                            601
                        );
                    case UPLOAD_ERR_PARTIAL:
                        throw new Exception(
                            'File was partially uploaded (UPLOAD_ERR_PARTIAL)',
                            601
                        );
                    case UPLOAD_ERR_NO_FILE:
                        throw new Exception(
                            'No file was uploaded (UPLOAD_ERR_NO_FILE)',
                            601
                        );
                    case UPLOAD_ERR_NO_TMP_DIR:
                        throw new Exception(
                            'Missing temp folder (UPLOAD_ERR_NO_TMP_DIR)',
                            601
                        );
                    case UPLOAD_ERR_CANT_WRITE:
                        throw new Exception(
                            'System write error (UPLOAD_ERR_CANT_WRITE)',
                            601
                        );
                    case UPLOAD_ERR_EXTENSION:
                        throw new Exception(
                            'The upload was stopped (UPLOAD_ERR_EXTENSION)',
                            601
                        );
                }
            }

            try {
                $renamed = rename($this->source['tmp_name'], $this->downstream);
            } catch (Throwable) {
                $renamed = file_exists($this->downstream);
            }
            if (! $renamed) {
                throw new Exception('Unable to rename tmp_name to downstream', 622);
            }
        } elseif ($this->type === 'url') {
            fetch_url($this->source, $this->downstream);
        }
        $this->source_filename = basename($this->type === 'file' ? $this->source['name'] : $this->source);
    }

    protected function validateSourceFile(): void
    {
        if (! file_exists($this->downstream)) {
            throw new Exception("Can't fetch target upload source (downstream)", 600);
        }
        $this->mediaType = str_starts_with($this->source['type'] ?? 'image/', 'video/')
            ? 'video'
            : 'image';
        $this->source_image_fileinfo = $this->mediaType === 'video'
            ? get_video_fileinfo($this->downstream)
            : get_image_fileinfo($this->downstream);
        if ($this->source_image_fileinfo === []) {
            throw new Exception("Can't get target upload source info", 610);
        }
        if ($this->source_image_fileinfo['width'] === ''
            || $this->source_image_fileinfo['height'] === ''
        ) {
            throw new Exception('Invalid image', 400);
        }
        if (! in_array($this->source_image_fileinfo['extension'], self::getAvailableImageFormats(), true)) {
            throw new Exception('Unavailable image format', 613);
        }
        if (! in_array($this->source_image_fileinfo['extension'], $this->options['allowed_formats'], true)) {
            throw new Exception(sprintf('Disabled image format (%s)', $this->source_image_fileinfo['extension']), 614);
        }
        if (! $this->isValidMime($this->source_image_fileinfo['mime'])) {
            throw new Exception('Invalid mimetype', 612);
        }
        if (! $this->options['max_size']) {
            $this->options['max_size'] = self::getDefaultOptions()['max_size'];
        }
        if ($this->source_image_fileinfo['size'] > $this->options['max_size']) {
            throw new Exception('File too big - max ' . format_bytes($this->options['max_size']), 400);
        }
        if ($this->source_image_fileinfo['extension'] === 'bmp') {
            $this->ImageConvert = new ImageConvert($this->downstream, 'png', $this->downstream);
            $this->downstream = $this->ImageConvert->out();
            $this->source_image_fileinfo = get_image_fileinfo($this->downstream);
        }
        if ($this->source_image_fileinfo['extension'] === 'webp'
            && is_animated_webp($this->downstream)
            && ImageManagerStatic::getManager()->config['driver'] === 'gd'
        ) {
            throw new Exception('Animated WebP is not supported', 400);
        }

        if ($this->mediaType === 'video') {
            return;
        }

        if (Settings::get('arachnid')) {
            $arachnid = new ProjectArachnid(
                apiUsername: Settings::get('arachnid_api_username') ?? '',
                apiPassword: Settings::get('arachnid_api_password') ?? '',
                filePath: $this->downstream
            );
            if ($arachnid->isSuccess()) {
                $arachnid->assertIsAllowed();
            } else {
                throw new Exception(
                    'Error processing Project Arachnid Shield moderation'
                    . ' : '
                    . $arachnid->errorMessage(),
                    600
                );
            }
        }

        if (Settings::get('moderatecontent')
            && (
                Settings::get('moderatecontent_block_rating') !== '' ||
                Settings::get('moderatecontent_flag_nsfw')
            )
        ) {
            $moderateContent = new ModerateContent($this->downstream, $this->source_image_fileinfo);
            if ($moderateContent->isSuccess()) {
                $this->moderation = $moderateContent->moderation();
            } else {
                throw new Exception('Error processing ModerateContent: ' . $moderateContent->errorMessage(), 610);
            }
        }
    }

    protected static function handleFlood(): array
    {
        if (! getSetting('flood_uploads_protection') || Login::isAdmin()) {
            return [];
        }
        $flood_limit = [];
        foreach (['minute', 'hour', 'day', 'week', 'month'] as $v) {
            $flood_limit[$v] = getSetting('flood_uploads_' . $v);
        }

        try {
            $db = DB::getInstance();
            $flood_db = $db->queryFetchSingle(
                'SELECT
				COUNT(IF(image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MINUTE), 1, NULL)) AS minute,
				COUNT(IF(image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 HOUR), 1, NULL)) AS hour,
				COUNT(IF(image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 DAY), 1, NULL)) AS day,
				COUNT(IF(image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 WEEK), 1, NULL)) AS week,
				COUNT(IF(image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH), 1, NULL)) AS month
			FROM ' . DB::getTable('images') . " WHERE image_uploader_ip='" . get_client_ip() . "' AND image_date_gmt >= DATE_SUB(UTC_TIMESTAMP(), INTERVAL 1 MONTH)"
            );
        } catch (Exception) {
            $flood_db = [];
        } // Silence
        if ($flood_db === false) {
            return [];
        }
        $is_flood = false;
        $flood_by = '';
        foreach (['minute', 'hour', 'day', 'week', 'month'] as $v) {
            if ($flood_limit[$v] > 0 && ($flood_db[$v] ?? 0) >= $flood_limit[$v]) {
                $flood_by = $v;
                $is_flood = true;

                break;
            }
        }
        if ($is_flood) {
            if (getSetting('flood_uploads_notify') && ! (session()['flood_uploads_notify'][$flood_by] ?? false)) {
                try {
                    $logged_user = Login::getUser();
                    $message = strtr('Flooding IP <a href="' . get_public_url('search/images/?q=ip:%ip') . '">%ip</a>', [
                        '%ip' => get_client_ip(),
                    ]) . '<br>';
                    if ($logged_user !== []) {
                        $message .= 'User <a href="' . $logged_user['public_url'] . '">' . $logged_user['name'] . '</a><br>';
                    }
                    $message .= '<br>';
                    $message .= '<b>Uploads per time period</b><br>';
                    $message .= 'Minute: ' . $flood_db['minute'] . '<br>';
                    $message .= 'Hour: ' . $flood_db['hour'] . '<br>';
                    $message .= 'Week: ' . $flood_db['day'] . '<br>';
                    $message .= 'Month: ' . $flood_db['week'] . '<br>';
                    system_notification_email([
                        'subject' => 'Flood report IP ' . get_client_ip(),
                        'message' => $message,
                    ]);
                    $addValues = session()['flood_uploads_notify'] ?? [];
                    $addValues[$flood_by] = true;
                    sessionVar()->put('flood_uploads_notify', $addValues);
                } catch (Exception) {
                } // Silence
            }

            return [
                'flood' => true,
                'limit' => $flood_limit[$flood_by],
                'count' => $flood_db[$flood_by],
                'by' => $flood_by,
            ];
        }

        return [];
    }

    protected function isValidMime(string $mime): bool
    {
        if (str_starts_with($mime, 'video/')) {
            return $this->isValidVideoMime($mime);
        }

        return $this->isValidImageMime($mime);
    }

    protected function isValidImageMime(string $mime): bool
    {
        if (str_starts_with($mime, 'video/')) {
            return $this->isValidVideoMime($mime);
        }

        return preg_match("#image\/(gif|pjpeg|jpeg|png|x-png|bmp|x-ms-bmp|x-windows-bmp|webp|avif)$#", $mime) === 1;
    }

    protected function isValidVideoMime(string $mime): bool
    {
        return preg_match("#video\/(quicktime|mp4|webm)$#", $mime) === 1;
    }

    protected function isValidNamingOption(string $string): bool
    {
        return in_array($string, ['mixed', 'random', 'original'], true);
    }
}
