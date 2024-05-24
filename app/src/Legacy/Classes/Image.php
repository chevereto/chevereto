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

use function Chevere\Message\message;
use function Chevere\String\randomString;
use Chevere\Throwable\Exceptions\LogicException;
use function Chevereto\Encryption\decrypt;
use function Chevereto\Encryption\hasEncryption;
use function Chevereto\Legacy\assertNotStopWords;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\add_ending_slash;
use function Chevereto\Legacy\G\array_filter_array;
use function Chevereto\Legacy\G\array_utf8encode;
use function Chevereto\Legacy\G\datetime;
use function Chevereto\Legacy\G\datetime_add;
use function Chevereto\Legacy\G\datetime_diff;
use function Chevereto\Legacy\G\datetime_modify;
use function Chevereto\Legacy\G\datetime_sub;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\G\datetimegmt_convert_tz;
use function Chevereto\Legacy\G\format_bytes;
use function Chevereto\Legacy\G\get_basename_without_extension;
use function Chevereto\Legacy\G\get_bytes;
use function Chevereto\Legacy\G\get_client_ip;
use function Chevereto\Legacy\G\get_ffmpeg_error;
use function Chevereto\Legacy\G\get_filename;
use function Chevereto\Legacy\G\get_image_fileinfo as GGet_image_fileinfo;
use function Chevereto\Legacy\G\get_public_url;
use function Chevereto\Legacy\G\is_animated_image;
use function Chevereto\Legacy\G\name_unique_file;
use function Chevereto\Legacy\G\nullify_string;
use function Chevereto\Legacy\G\safe_html;
use function Chevereto\Legacy\G\seoUrlfy;
use function Chevereto\Legacy\G\starts_with;
use function Chevereto\Legacy\G\truncate;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\G\url_to_relative;
use function Chevereto\Legacy\get_fileinfo;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\system_notification_email;
use function Chevereto\Legacy\time_elapsed_string;
use function Chevereto\Vars\env;
use function Chevereto\Vars\session;
use function Chevereto\Vars\sessionVar;
use DateTimeZone;
use Exception;
use FFMpeg\Coordinate\TimeCode;
use FFMpeg\FFMpeg;
use Intervention\Image\ImageManagerStatic;
use PHPExif\Exif;
use function Safe\password_hash;
use Throwable;

class Image
{
    public static array $table_chv_image = [
        'name',
        'extension',
        'album_id',
        'size',
        'width',
        'height',
        'date',
        'date_gmt',
        'nsfw',
        'user_id',
        'uploader_ip',
        'storage_mode',
        'storage_id',
        'md5',
        'source_md5',
        'original_filename',
        'original_exifdata',
        'category_id',
        'description',
        'chain',
        'thumb_size',
        'medium_size',
        'frame_size',
        'title',
        'expiration_date_gmt',
        'likes',
        'is_animated',
        'is_approved',
        'is_360',
        'duration',
        'type'
    ];

    protected static array $expirations = [
        ['minute', 5, 300],
        ['minute', 15, 900],
        ['minute', 30, 1800],
        ['hour', 1, 3600],
        ['hour', 3, 10800],
        ['hour', 6, 21600],
        ['hour', 12, 43200],
        ['day', 1, 86400],
        ['day', 2, 172800],
        ['day', 3, 259200],
        ['day', 4, 345600],
        ['day', 5, 432000],
        ['day', 6, 518400],
        ['week', 1, 604800],
        ['week', 2, 1209600],
        ['week', 3, 1814400],
        ['month', 1, 2630000],
        ['month', 2, 5260000],
        ['month', 3, 7890000],
        ['month', 4, 10520000],
        ['month', 5, 13150000],
        ['month', 6, 15780000],
        ['year', 1, 31536000],
    ];

    public static array $types = [
        1 => 'image',
        2 => 'video',
        3 => 'audio',
    ];

    public static array $chain_sizes = [
        'frame',    // 2^4
        'original', // 2^3
        'image',    // 2^2
        'medium',   // 2^1
        'thumb',    // 2^0
    ];

    public static function getSingle(
        int $id,
        bool $sumView = false,
        bool $pretty = false,
        array $requester = []
    ): array {
        $tables = DB::getTables();
        $query = 'SELECT * FROM ' . $tables['images'] . "\n";
        $joins = [
            'LEFT JOIN ' . $tables['storages'] . ' ON ' . $tables['images'] . '.image_storage_id = ' . $tables['storages'] . '.storage_id',
            'LEFT JOIN ' . $tables['storage_apis'] . ' ON ' . $tables['storages'] . '.storage_api_id = ' . $tables['storage_apis'] . '.storage_api_id',
            'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['images'] . '.image_user_id = ' . $tables['users'] . '.user_id',
            'LEFT JOIN ' . $tables['albums'] . ' ON ' . $tables['images'] . '.image_album_id = ' . $tables['albums'] . '.album_id'
        ];
        if ($requester !== []) {
            if (version_compare(Settings::get('chevereto_version_installed'), '3.7.0', '>=')) {
                $joins[] = 'LEFT JOIN ' . $tables['likes'] . ' ON ' . $tables['likes'] . '.like_content_type = "image" AND ' . $tables['images'] . '.image_id = ' . $tables['likes'] . '.like_content_id AND ' . $tables['likes'] . '.like_user_id = ' . $requester['id'];
            }
        }
        $query .= implode("\n", $joins) . "\n";
        $query .= 'WHERE image_id=:image_id;' . "\n";
        if ($sumView) {
            $query .= 'UPDATE ' . $tables['images'] . ' SET image_views = image_views + 1 WHERE image_id=:image_id';
        }
        $db = DB::getInstance();
        $db->query($query);
        $db->bind(':image_id', $id);
        $image_db = $db->fetchSingle();
        if (empty($image_db)) {
            return [];
        }
        if ($sumView) {
            $image_db['image_views'] += 1;
            Stat::track([
                'action' => 'update',
                'table' => 'images',
                'value' => '+1',
                'user_id' => $image_db['image_user_id'],
            ]);
        }
        if ($requester !== []) {
            $image_db['image_liked'] = (bool) $image_db['like_user_id'];
        }
        $return = $image_db;
        $return = $pretty ? self::formatArray($return) : $return;
        if (!isset($return['file_resource'])) {
            $return['file_resource'] = self::getSrcTargetSingle($image_db);
        }

        return $return;
    }

    public static function getMultiple(array $ids, bool $pretty = false): array
    {
        if ($ids === []) {
            throw new Exception('Null $ids provided in Image::get_multiple', 600);
        }
        $tables = DB::getTables();
        $query = 'SELECT * FROM ' . $tables['images'] . "\n";
        $joins = [
            'LEFT JOIN ' . $tables['users'] . ' ON ' . $tables['images'] . '.image_user_id = ' . $tables['users'] . '.user_id',
            'LEFT JOIN ' . $tables['albums'] . ' ON ' . $tables['images'] . '.image_album_id = ' . $tables['albums'] . '.album_id'
        ];
        $query .= implode("\n", $joins) . "\n";
        $query .= 'WHERE image_id IN (' . implode(',', $ids) . ')' . "\n";
        $db = DB::getInstance();
        $db->query($query);
        $images_db = $db->fetchAll();
        if (!empty($images_db)) {
            foreach ($images_db as $k => $v) {
                $images_db[$k] = array_merge($v, self::getSrcTargetSingle($v, true)); // todo
            }
        }
        if ($pretty) {
            $return = [];
            foreach ($images_db as $k => $v) {
                $return[] = self::formatArray($v);
            }

            return $return;
        }

        return $images_db;
    }

    public static function getAlbumSlice(
        int $image_id,
        int $album_id = null,
        int $padding = 2
    ): array {
        $tables = DB::getTables();
        if (!isset($album_id)) {
            $db = DB::getInstance();
            $db->query('SELECT image_album_id FROM ' . $tables['images'] . ' WHERE image_id=:image_id');
            $db->bind(':image_id', $image_id);
            $image_album_db = $db->fetchSingle();
            $album_id = $image_album_db['image_album_id'];
            if (!isset($album_id)) {
                return [];
            }
        }
        if (!is_numeric($padding)) {
            $padding = 2;
        }
        $prevListing = new Listing();
        $prevListing->setType('images');
        $prevListing->setLimit(($padding * 2) + 1);
        $prevListing->setSortType('date');
        $prevListing->setSortOrder('desc');
        $prevListing->setRequester(Login::getUser());
        $prevListing->setWhere('WHERE image_album_id=' . $album_id . ' AND image_id <= ' . $image_id);
        $prevListing->exec();
        $nextListing = new Listing();
        $nextListing->setType('images');
        $nextListing->setLimit($padding * 2);
        $nextListing->setSortType('date');
        $nextListing->setSortOrder('asc');
        $nextListing->setRequester(Login::getUser());
        $nextListing->setWhere('WHERE image_album_id=' . $album_id . ' AND image_id > ' . $image_id);
        $nextListing->exec();
        if (is_array($prevListing->output)) {
            $prevListing->output = array_reverse($prevListing->output);
        }
        $list = array_merge($prevListing->output, $nextListing->output);
        $album_offset = [
            'top' => $prevListing->count - 1,
            'bottom' => $nextListing->count
        ];
        $album_chop_count = count($list);
        $album_iteration_times = $album_chop_count - ($padding * 2 + 1);
        if ($album_chop_count > ($padding * 2 + 1)) {
            if ($album_offset['top'] > $padding && $album_offset['bottom'] > $padding) {
                for ($i = 0; $i < $album_offset['top'] - $padding; $i++) {
                    unset($list[$i]);
                }
                for ($i = 1; $i <= $album_offset['bottom'] - $padding; $i++) {
                    unset($list[$album_chop_count - $i]);
                }
            } elseif ($album_offset['top'] <= $padding) {
                for ($i = 0; $i < $album_iteration_times; $i++) {
                    unset($list[$album_chop_count - 1 - $i]);
                }
            } elseif ($album_offset['bottom'] <= $padding) {
                for ($i = 0; $i < $album_iteration_times; $i++) {
                    unset($list[$i]);
                }
            }
            $list = array_values($list);
        }
        $images = [];
        foreach ($list as $v) {
            $format = self::formatArray($v);
            $images[$format['id']] = $format;
        }
        if (is_array($prevListing->output) && $prevListing->count > 1) {
            $prevLastKey = $prevListing->count - 2;
            $prevLastId = $prevListing->output[$prevLastKey]['image_id'];
            $slice['prev'] = $images[$prevLastId];
        }
        if ($nextListing->output) {
            $slice['next'] = $images[$nextListing->output[0]['image_id']];
        }
        $slice['images'] = $images;

        return $slice;
    }

    public static function getSrcTargetSingle(array $fileArray, bool $prefix = true): array
    {
        $prefix = $prefix ? 'image_' : null;
        $folder = CHV_PATH_IMAGES;
        $pretty = !isset($fileArray['image_id']);
        $mode = $fileArray[$prefix . 'storage_mode'];
        $chain_mask = str_split(
            (string) str_pad(
                decbin((int) ($fileArray[$pretty ? 'chain' : 'image_chain'])),
                5,
                '0',
                STR_PAD_LEFT
            )
        );
        $chain_to_suffix = [
            'image' => '.',
            'frame' => '.fr.',
            'medium' => '.md.',
            'thumb' => '.th.',
        ];
        if ($pretty) {
            $type = isset($fileArray['storage']['id']) ? 'url' : 'path';
        } else {
            $type = isset($fileArray['storage_id']) ? 'url' : 'path';
        }
        if ($type == 'url') {
            $folder = add_ending_slash($pretty ? $fileArray['storage']['url'] : $fileArray['storage_url']);
        }
        switch ($mode) {
            case 'datefolder':
                $datetime = $fileArray[$prefix . 'date'];
                $datefolder = preg_replace('/(.*)(\s.*)/', '$1', str_replace('-', '/', $datetime));
                $folder .= add_ending_slash($datefolder); // Y/m/d/

                break;
            case 'old':
                $folder .= 'old/';

                break;
            case 'direct':
                // use direct $folder
                break;
            case 'path':
                $folder = add_ending_slash($fileArray['path']);

                break;
        }
        $targets = [
            'type' => $type,
            'chain' => [
                'frame' => null,
                'image' => null,
                'thumb' => null,
                'medium' => null
            ]
        ];
        foreach (array_keys($targets['chain']) as $k) {
            $extension = $fileArray[$prefix . 'extension'];
            if ($k !== 'image' && in_array($extension, ['mov', 'mp4', 'webm'])) {
                $extension = 'jpeg';
            }
            $targets['chain'][$k] = $folder . $fileArray[$prefix . 'name'] . $chain_to_suffix[$k] . $extension;
        }
        if ($type == 'path') {
            foreach ($targets['chain'] as $k => $v) {
                if (!is_readable($v)) {
                    unset($targets['chain'][$k]);
                }
            }
        } else {
            foreach ($chain_mask as $k => $v) {
                if (!(bool) $v) {
                    unset($targets['chain'][self::$chain_sizes[$k]]);
                }
            }
        }

        return $targets;
    }

    public static function getUrlViewer(string $id_encoded, string $title = ''): string
    {
        $seo = seoUrlfy($title);
        $url = $seo == ''
            ? $id_encoded
            : ($seo . '.' . $id_encoded);

        return get_public_url(
            (getSetting('root_route') === 'image'
                ? ''
                : getSetting('route_image') . '/')
            . $url,
        );
    }

    public static function getDeleteUrl(string $idEncoded, string $password): string
    {
        return self::getUrlViewer($idEncoded) . '/delete/' . $password;
    }

    public static function getAvailableExpirations(): array
    {
        $string = _s('After %n %t');
        $translate = [
            'minute' => _n('minute', 'minutes', 1),
            'hour' => _n('hour', 'hours', 1),
            'day' => _n('day', 'days', 1),
            'week' => _n('week', 'weeks', 1),
            'month' => _n('month', 'months', 1),
            'year' => _n('year', 'years', 1),
        ];
        $return = [
            null => _s("Don't autodelete"),
        ];
        $table = self::$expirations;
        foreach ($table as $expire) {
            $unit = $expire[0];
            $interval_spec = self::getPastTimeSpec($unit, $expire[1]);
            $return[$interval_spec] = strtr($string, ['%n' => $expire[1], '%t' => _n($unit, $unit . 's', $expire[1])]);
        }

        return $return;
    }

    protected static function getPastTimeSpec(string $unit, string $value): string
    {
        return 'P' .
            (in_array($unit, ['second', 'minute', 'hour'])
                ? 'T'
                : '')
            . $value . strtoupper($unit[0]);
    }

    public static function getExpirationFromSeconds(int $seconds): string
    {
        if ($seconds <= 0) {
            return '';
        }
        $previous = array_values(self::$expirations)[0];
        foreach (self::$expirations as $expires) {
            if ($seconds < $expires[2]) {
                return self::getPastTimeSpec(...$previous);
            }
            $previous = [strval($expires[0]), strval($expires[1])];
        }
        $maxLimit = self::$expirations[count(self::$expirations) - 1];

        return self::getPastTimeSpec(...$maxLimit);
    }

    public static function watermarkFromDb(): void
    {
        $file = PATH_PUBLIC_CONTENT_IMAGES_SYSTEM . getSetting('watermark_image');
        $assetsDb = DB::get('assets', ['key' => 'watermark_image'], 'AND', [], 1);
        if ($assetsDb === false) {
            return;
        }
        if (file_exists($file)
            && md5_file($file) != $assetsDb['asset_md5']
            && !starts_with('default/', getSetting('watermark_image'))
        ) {
            unlinkIfExists($file);
        }
        if (!file_exists($file)) {
            $fh = fopen($file, 'w');
            $st = !$fh || fwrite($fh, $assetsDb['asset_blob']) === false ? false : true;
            fclose($fh);
            if (!$st) {
                throw new LogicException(
                    message(_s("Can't open %s for writing", $file)),
                    600
                );
            }
        }
    }

    public static function watermark(string $image_path, array $options = []): bool
    {
        $options = array_merge([
            'ratio' => getSetting('watermark_percentage') / 100,
            'position' => explode(' ', getSetting('watermark_position')),
            'file' => PATH_PUBLIC_CONTENT_IMAGES_SYSTEM . getSetting('watermark_image')
        ], $options);
        self::watermarkFromDb();
        if (!is_readable($options['file'])) {
            throw new Exception("Can't read watermark file at " . $options['file'], 600);
        }
        $image = ImageManagerStatic::make($image_path);
        $options['ratio'] = min(1, (is_numeric($options['ratio']) ? max(0.01, $options['ratio']) : 0.01));
        if (!in_array($options['position'][0], ['left', 'center', 'right'])) {
            $options['position'][0] = 'right';
        }
        if (!in_array($options['position'][1], ['top', 'center', 'bottom'])) {
            $options['position'][0] = 'bottom';
        }
        $watermarkPos = [];
        if ($options['position'][1] !== 'center') {
            $watermarkPos[] = $options['position'][1];
        }
        if ($options['position'][0] !== 'center') {
            $watermarkPos[] = $options['position'][0];
        }
        $watermark = ImageManagerStatic::make($options['file']);
        $watermark_area = $image->getWidth() * $image->getHeight() * $options['ratio'];
        $watermark_image_ratio = $watermark->getWidth() / $watermark->getHeight();
        $watermark_new_height = round(sqrt($watermark_area / $watermark_image_ratio), 0);
        if ($watermark_new_height > $image->getHeight()) {
            $watermark_new_height = $image->getHeight();
        }
        if (getSetting('watermark_margin') && $options['position'][1] !== 'center' && $watermark_new_height + getSetting('watermark_margin') > $image->getHeight()) {
            $watermark_new_height -= $watermark_new_height + 2 * getSetting('watermark_margin') - $image->getHeight();
        }
        $watermark_new_width = round($watermark_image_ratio * $watermark_new_height, 0);
        if ($watermark_new_width > $image->getWidth()) {
            $watermark_new_width = $image->getWidth();
        }
        if (getSetting('watermark_margin') && $options['position'][0] !== 'center' && $watermark_new_width + getSetting('watermark_margin') > $image->getWidth()) {
            $watermark_new_width -= $watermark_new_width + 2 * getSetting('watermark_margin') - $image->getWidth();
            $watermark_new_height = $watermark_new_width / $watermark_image_ratio;
        }
        if ($watermark_new_width !== $watermark->getWidth()) {
            $watermark->resize($watermark_new_width, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            });
        }
        $watermark->opacity(getSetting('watermark_opacity'));
        $image
            ->insert(
                $watermark,
                $watermarkPos === []
                    ? 'center'
                    : implode('-', $watermarkPos),
                getSetting('watermark_margin'),
                getSetting('watermark_margin')
            )
            ->save();

        return true;
    }

    public static function upload(
        array|string $source,
        string $destination,
        string|null $filename = null,
        array $options = [],
        int|null $storage_id = null,
        bool $guestSessionHandle = true
    ): array {
        $default_options = Upload::getDefaultOptions();
        $options = array_merge($default_options, $options);
        if (!is_null($filename) && !$options['filenaming']) {
            $options['filenaming'] = 'original';
        }
        $upload = new Upload();
        $upload->setSource($source);
        $upload->setDestination($destination);
        $upload->setOptions($options);
        if (!is_null($storage_id)) {
            $upload->setStorageId($storage_id);
        }
        if (!is_null($filename)) {
            $upload->setFilename($filename);
        }
        if ($guestSessionHandle == false) {
            $upload->detectFlood = false;
        }
        $upload->exec();

        return [
            'uploaded' => $upload->uploaded(),
            'source' => $upload->source(),
            'moderation' => $upload->moderation(),
        ];
    }

    // Mostly for people uploading two times the same image to test or just bug you
    // $mixed => $_FILES or md5 string
    public static function isDuplicatedUpload(array|string $source, string $timePeriod = 'P1D'): bool
    {
        if (is_array($source) && isset($source['tmp_name'])) {
            $filename = $source['tmp_name'];
            if (stream_resolve_include_path($filename) == false) {
                throw new Exception("Concurrency: $filename is gone", 666);
            }
            $md5_file = md5_file($filename);
        } else {
            $filename = $source;
            $md5_file = $filename;
        }
        if ($md5_file === false) {
            throw new Exception('Unable to process md5_file', 600);
        }
        $db = DB::getInstance();
        $db->query('SELECT * FROM ' . DB::getTable('images') . ' WHERE (image_md5=:md5 OR image_source_md5=:md5) AND image_uploader_ip=:ip AND image_date_gmt > :date_gmt');
        $db->bind(':md5', $md5_file);
        $db->bind(':ip', get_client_ip());
        $db->bind(':date_gmt', datetime_sub(datetimegmt(), $timePeriod));
        $db->exec();

        return (bool) $db->fetchColumn();
    }

    public static function uploadToWebsite(
        array|string $source,
        array $user = [],
        array $params = [],
        bool $guestSessionHandle = true,
        string|null $ip = null
    ): array {
        $params['use_file_date'] = $params['use_file_date'] ?? false;
        nullify_string($params['album_id']);
        $dateFolder = '';

        try {
            if ($user !== []
                && getSetting('upload_max_filesize_mb_bak') !== null
                && getSetting('upload_max_filesize_mb') == getSetting('upload_max_filesize_mb_guest')
            ) {
                Settings::setValue('upload_max_filesize_mb', getSetting('upload_max_filesize_mb_bak'));
            }
            $do_dupe_check = !getSetting('enable_duplicate_uploads') && !($user['is_admin'] ?? false);
            if ($do_dupe_check && self::isDuplicatedUpload($source)) {
                throw new Exception(_s('Duplicated upload'), 101);
            }
            $storage_id = null;
            $upload_types = [
                'image' => 1,
                'video' => 2,
                // 'audio' => 4,
                // 'document' => 8,
                // 'other' => 16,
            ];
            $mimetype = strtok($params['mimetype'] ?? 'image', '/');
            $type_chain = $upload_types[$mimetype] ?? 1;
            $get_active_storages = env()['CHEVERETO_ENABLE_EXTERNAL_STORAGE']
                ? Storage::get([
                    'is_active' => 1,
                    'type_chain' => $type_chain
                ])
                : [];
            if ($get_active_storages !== []) {
                if (count($get_active_storages) > 1) {
                    $last_used_storage = (int) getSetting('last_used_storage');
                } else {
                    $last_used_storage = null;
                    $storage_id = (int) $get_active_storages[0]['id'];
                }
                $last_used_storage_is_active = false;
                $active_storages = [];
                foreach ($get_active_storages as $i => $get_active_storage) {
                    $pointer = $get_active_storage['id'];
                    $active_storages[$pointer] = $get_active_storage;
                    if ($pointer === $last_used_storage) {
                        $last_used_storage_is_active = true;
                    }
                }
                if (!$last_used_storage_is_active) {
                    $storage_id = $get_active_storages[0]['id'];
                } else {
                    unset($active_storages[$last_used_storage]);
                    $storage_keys = array_keys($active_storages);
                    shuffle($storage_keys);
                    $storage_id = $storage_keys[0];
                }
                $storage = $active_storages[$storage_id];
            }
            $storage_mode = getSetting('upload_storage_mode');
            $upload_path = '';
            switch ($storage_mode) {
                case 'direct':
                    $upload_path = CHV_PATH_IMAGES;

                    break;
                case 'datefolder':
                    $stockDate = datetime();
                    $stockDateGmt = datetimegmt();
                    if (is_array($source) && $params['use_file_date'] && $source['type'] === 'image/jpeg') {
                        try {
                            $exifSource = \exif_read_data($source['tmp_name']);
                        } catch (Throwable) {
                        }
                        if (isset($exifSource['DateTime'])) {
                            $stockDateGmt = date_create_from_format("Y:m:d H:i:s", $exifSource['DateTime'], new DateTimeZone('UTC'));
                            $stockDateGmt = $stockDateGmt->format('Y-m-d H:i:s');
                            $stockDate = datetimegmt_convert_tz($stockDateGmt, getSetting('default_timezone'));
                        }
                    }
                    $datefolder_stock = [
                        'date' => $stockDate,
                        'date_gmt' => $stockDateGmt,
                    ];
                    $dateFolder = date('Y/m/d/', strtotime($datefolder_stock['date']));
                    $upload_path = CHV_PATH_IMAGES . $dateFolder;

                    break;
            }
            $fileNaming = getSetting('upload_filenaming');
            if ($fileNaming !== 'id' && in_array($params['privacy'] ?? '', ['password', 'private', 'private_but_link'])) {
                $fileNaming = 'random';
            }
            $upload_options = [
                'max_size' => get_bytes(getSetting('upload_max_filesize_mb') . ' MB'),
                'exif' => (getSetting('upload_image_exif_user_setting') && $user !== [])
                    ? $user['image_keep_exif']
                    : getSetting('upload_image_exif'),
            ];
            if ($fileNaming == 'id') {
                try {
                    $dummy = [
                        'name' => '',
                        'extension' => '',
                        'size' => 0,
                        'width' => 0,
                        'height' => 0,
                        'date' => '0000-01-01 00:00:00',
                        'date_gmt' => '0000-01-01 00:00:00',
                        'nsfw' => 0,
                        'uploader_ip' => '',
                        'md5' => '',
                        'original_filename' => '',
                        'chain' => 0,
                        'thumb_size' => 0,
                        'medium_size' => 0,
                        'frame_size' => 0,
                        'duration' => 0,
                    ];
                    $dummy_insert = DB::insert('images', $dummy);
                    DB::delete('images', ['id' => $dummy_insert]);
                    $target_id = $dummy_insert;
                } catch (Throwable) {
                    $fileNaming = 'original';
                }
            }
            $upload_options['filenaming'] = $fileNaming;
            $upload_options['allowed_formats'] = self::getEnabledImageExtensions();
            $image_upload = self::upload(
                $source,
                $upload_path,
                ($fileNaming == 'id' && isset($target_id))
                    ? encodeID((int) $target_id)
                    : null,
                $upload_options,
                $storage_id,
                $guestSessionHandle
            );
            $chain_mask = [0, 0, 1, 0, 1]; // frame, original, image, medium, thumb
            if ($do_dupe_check && self::isDuplicatedUpload($image_upload['uploaded']['fileinfo']['md5'])) {
                throw new Exception(_s('Duplicated upload'), 102);
            }
            $image_ratio = $image_upload['uploaded']['fileinfo']['ratio'];
            $must_resize = false;
            $image_max_size_cfg = [
                'width' => Settings::get('upload_max_image_width') ?: $image_upload['uploaded']['fileinfo']['width'],
                'height' => Settings::get('upload_max_image_height') ?: $image_upload['uploaded']['fileinfo']['height'],
            ];
            if ($image_max_size_cfg['width'] < $image_upload['uploaded']['fileinfo']['width'] || $image_max_size_cfg['height'] < $image_upload['uploaded']['fileinfo']['height']) {
                $image_max = $image_max_size_cfg;
                $image_max['width'] = (int) round($image_max_size_cfg['height'] * $image_ratio);
                $image_max['height'] = (int) round($image_max_size_cfg['width'] / $image_ratio);
                if ($image_max['height'] > $image_max_size_cfg['height']) {
                    $image_max['height'] = $image_max_size_cfg['height'];
                    $image_max['width'] = (int) round($image_max['height'] * $image_ratio);
                }
                if ($image_max['width'] > $image_max_size_cfg['width']) {
                    $image_max['width'] = $image_max_size_cfg['width'];
                    $image_max['height'] = (int) round($image_max['width'] / $image_ratio);
                }
                if ($image_max !== ['width' => $image_upload['uploaded']['fileinfo']['width'], 'height' => $image_max_size_cfg['height']]) { // loose just in case..
                    $must_resize = true;
                    $params['width'] = $image_max['width'];
                    $params['height'] = $image_max['height'];
                }
            }
            foreach (['width', 'height'] as $k) {
                if (!isset($params[$k]) || !is_numeric($params[$k])) {
                    continue;
                }
                if ($params[$k] != $image_upload['uploaded']['fileinfo'][$k]) {
                    $must_resize = true;
                }
            }
            $is_360 = (bool) $image_upload['uploaded']['fileinfo']['is_360'];
            if (is_animated_image($image_upload['uploaded']['file'])) {
                $must_resize = false;
            }
            $resizeSourceImage = $image_upload['uploaded']['file'];
            $uploadDir = dirname($resizeSourceImage);
            $chainExtension = $image_upload['uploaded']['extension'];
            if ($image_upload['source']['type'] === 'video') {
                $must_resize = false;
                $chainExtension = 'jpeg';
                $frameImage = $uploadDir
                    . '/'
                    . $image_upload['uploaded']['name']
                    . '.fr.'
                    . $chainExtension;
                rename($image_upload['uploaded']['frame'], $frameImage);
                chmod($frameImage, 0644);
                $resizeSourceImage = $frameImage;
                $chain_mask[0] = 1;
            }
            if ($must_resize) {
                $source_md5 = $image_upload['uploaded']['fileinfo']['md5'];
                if ($do_dupe_check && self::isDuplicatedUpload($source_md5)) {
                    throw new Exception(_s('Duplicated upload'), 103);
                }
                $image_ratio = $image_upload['uploaded']['fileinfo']['ratio'];
                if (isset($params['width'], $params['height'])) {
                    $image_resize_options = [
                        'width' => $params['width'],
                        'height' => $params['height'],
                    ];
                } else {
                    $image_resize_options = ['width' => $params['width']];
                }
                $image_resize_options['extension'] = $image_upload['uploaded']['extension'];
                $image_resize_options['chmod'] = 0644;
                $image_upload['uploaded'] = self::resize(
                    source: $resizeSourceImage,
                    destination: dirname($resizeSourceImage),
                    filename: null,
                    options: $image_resize_options
                );
                $image_upload['uploaded']['fileinfo']['is_360'] = $is_360;
            }
            $image_thumb_options = [
                'forced' => true,
                'over_resize' => true,
                'fitted' => true,
                'width' => getSetting('upload_thumb_width'),
                'height' => getSetting('upload_thumb_height'),
                'extension' => $chainExtension,
                'chmod' => 0644,
            ];
            $medium_size = getSetting('upload_medium_size');
            $medium_fixed_dimension = getSetting('upload_medium_fixed_dimension');
            $is_animated_image = is_animated_image($image_upload['uploaded']['file']);
            $image_thumb = self::resize(
                source: $resizeSourceImage,
                destination: $uploadDir,
                filename: $image_upload['uploaded']['name'] . '.th',
                options: $image_thumb_options
            );
            $original_md5 = $image_upload['source']['fileinfo']['md5'];
            $watermark_enable = getSetting('watermark_enable');
            if ($watermark_enable) {
                $watermark_user = $user !== []
                    ? ($user['is_admin'] ? 'admin' : 'user')
                    : 'guest';
                $watermark_enable = getSetting('watermark_enable_' . $watermark_user);
            }
            $watermark_gif = (bool) getSetting('watermark_enable_file_gif');
            $apply_watermark = $watermark_enable;
            if ($is_animated_image || $image_upload['uploaded']['fileinfo']['is_360']) {
                $apply_watermark = false;
            }
            if ($apply_watermark) {
                foreach (['width', 'height'] as $k) {
                    $min_value = getSetting('watermark_target_min_' . $k);
                    if ($min_value == 0) { // Skip on zero
                        continue;
                    }
                    $apply_watermark = $image_upload['uploaded']['fileinfo'][$k] >= $min_value;
                }
                if ($apply_watermark && $image_upload['uploaded']['fileinfo']['extension'] == 'gif' && !$watermark_gif) {
                    $apply_watermark = false;
                }
            }
            if ($apply_watermark && self::watermark($resizeSourceImage)) {
                $image_upload['uploaded']['fileinfo'] = GGet_image_fileinfo($resizeSourceImage); // Remake the fileinfo array, new full array file info (todo: faster!)
                $image_upload['uploaded']['fileinfo']['md5'] = $original_md5; // Preserve original MD5 for watermarked images
            }
            if ($image_upload['uploaded']['fileinfo'][$medium_fixed_dimension] > $medium_size || $is_animated_image) {
                $image_medium_options = [
                    'chmod' => 0644,
                ];
                $image_medium_options[$medium_fixed_dimension] = $medium_size;
                if ($is_animated_image) {
                    $image_medium_options['forced'] = true;
                    $image_medium_options[$medium_fixed_dimension] = min($image_medium_options[$medium_fixed_dimension], $image_upload['uploaded']['fileinfo'][$medium_fixed_dimension]);
                }
                $image_medium_options['extension'] = $chainExtension;
                $image_medium = self::resize(
                    source: $resizeSourceImage,
                    destination: $uploadDir,
                    filename: $image_upload['uploaded']['name'] . '.md',
                    options: $image_medium_options
                );
                $chain_mask[3] = 1;
            }
            $chain_value = bindec((string) implode('', $chain_mask));
            $disk_space_needed = $image_upload['uploaded']['fileinfo']['size'];
            if (isset($image_thumb['fileinfo']['size'])) {
                $disk_space_needed += $image_thumb['fileinfo']['size'];
            }
            if (isset($image_medium['fileinfo']['size'])) {
                $disk_space_needed += $image_medium['fileinfo']['size'];
            }
            $switch_to_local = false;
            if (isset($storage_id)
                && !empty($storage['capacity'])
                && $disk_space_needed > ($storage['capacity'] - $storage['space_used'])
                ) {
                if (isset($active_storages) && $active_storages !== []) {
                    $capable_storages = [];
                    foreach ($active_storages as $k => $v) {
                        if ($v['id'] == $storage_id || $disk_space_needed > ($v['capacity'] - $v['space_used'])) {
                            continue;
                        }
                        $capable_storages[] = $v['id'];
                    }
                    if (count($capable_storages) == 0) {
                        $switch_to_local = true;
                    } else {
                        $storage_id = (int) $capable_storages[0];
                        $storage = $active_storages[$storage_id];
                    }
                } else {
                    $switch_to_local = true;
                }
                if ($switch_to_local) {
                    $storage_id = 0;
                    $downstream = $image_upload['uploaded']['file'];
                    $fixed_filename = $image_upload['uploaded']['filename'];
                    $uploaded_file = name_unique_file(
                        $upload_path,
                        $fixed_filename,
                        $upload_options['filenaming']
                    );

                    try {
                        $renamed_uploaded = rename($downstream, $uploaded_file);
                    } catch (Throwable) {
                        $renamed_uploaded = file_exists($uploaded_file);
                    }
                    if (!$renamed_uploaded) {
                        throw new Exception("Can't re-allocate image to local storage", 600);
                    }
                    $image_upload['uploaded'] = [
                        'file' => $uploaded_file,
                        'filename' => get_filename($uploaded_file),
                        'name' => get_basename_without_extension($uploaded_file),
                        'fileinfo' => GGet_image_fileinfo($uploaded_file)
                    ];
                    $chain_props = [
                        'thumb' => ['suffix' => 'th'],
                        'medium' => ['suffix' => 'md']
                    ];
                    if (!($image_medium ?? false)) {
                        unset($chain_props['medium']);
                    }
                    $dirChain = dirname($image_upload['uploaded']['file']);
                    foreach ($chain_props as $k => $v) {
                        $chain_file = add_ending_slash($dirChain) . $image_upload['uploaded']['name'] . '.' . $v['suffix'] . '.' . ${"image_$k"}['fileinfo']['extension'];

                        try {
                            $renamed_chain = rename(${"image_$k"}['file'], $chain_file);
                        } catch (Throwable) {
                            $renamed_chain = file_exists($chain_file);
                        }
                        if (!$renamed_chain) {
                            throw new Exception("Can't re-allocate image " . $k . " to local storage", 601);
                        }
                        ${"image_$k"} = [
                            'file' => $chain_file,
                            'filename' => get_filename($chain_file),
                            'name' => get_basename_without_extension($chain_file),
                            'fileinfo' => GGet_image_fileinfo($chain_file)
                        ];
                    }
                }
            }
            $image_insert_values = [
                'storage_mode' => $storage_mode,
                'storage_id' => $storage_id ?? null,
                'user_id' => $user['id'] ?? null,
                'album_id' => $params['album_id'] ?? null,
                'nsfw' => $params['nsfw'] ?? null,
                'category_id' => $params['category_id'] ?? null,
                'title' => $params['title'] ?? null,
                'description' => $params['description'] ?? null,
                'chain' => $chain_value,
                'thumb_size' => $image_thumb['fileinfo']['size'] ?? 0,
                'medium_size' => $image_medium['fileinfo']['size'] ?? 0,
                'frame_size' => $image_upload['uploaded']['frameinfo']['size'] ?? 0,
                'is_animated' => $is_animated_image,
                'source_md5' => $source_md5 ?? null,
                'is_360' => $is_360,
                'duration' => $image_upload['uploaded']['fileinfo']['duration'] ?? 0,
            ];
            if (isset($datefolder_stock)) {
                foreach ($datefolder_stock as $k => $v) {
                    $image_insert_values[$k] = $v;
                }
            }
            if (getSetting('enable_expirable_uploads')) {
                if ($user === [] && getSetting('auto_delete_guest_uploads') !== null) {
                    $params['expiration'] = getSetting('auto_delete_guest_uploads');
                }
                if (!isset($params['expiration']) && isset($user['image_expiration'])) {
                    $params['expiration'] = $user['image_expiration'];
                }

                try {
                    if (!empty($params['expiration']) && array_key_exists($params['expiration'], self::getAvailableExpirations())) {
                        $params['expiration_date_gmt'] = datetime_add(datetimegmt(), strtoupper($params['expiration']));
                    }
                    if (!empty($params['expiration_date_gmt'])) {
                        $expirable_diff = datetime_diff(datetimegmt(), $params['expiration_date_gmt'], 'm');
                        $image_insert_values['expiration_date_gmt'] = $expirable_diff < 5 ? datetime_modify(datetimegmt(), '+5 minutes') : $params['expiration_date_gmt'];
                    }
                } catch (Exception) {
                } // Silence
            }
            if (isset($storage_id, $storage)) {
                $toStorage = [];
                foreach (self::$chain_sizes as $k => $v) {
                    if (!(bool) $chain_mask[$k]) {
                        continue;
                    }
                    switch ($v) {
                        case 'image':
                            $prop = $image_upload['uploaded'];

                            break;
                        case 'frame':
                            $prop = [
                                'file' => $frameImage,
                                'filename' => basename($frameImage),
                                'fileinfo' => $image_upload['uploaded']['frameinfo']
                            ];

                            break;
                        default:
                            $prop = ${"image_$v"};

                            break;
                    }
                    $toStorage[$v] = [
                        'file' => $prop['file'],
                        'filename' => $prop['filename'],
                        'mime' => $prop['fileinfo']['mime'],
                    ];
                }
                Storage::uploadFiles($toStorage, $storage, [
                    'keyprefix' => $storage_mode == 'datefolder'
                        ? $dateFolder
                        : null
                ]);
            }
            $image_title = $params['title']
                ?? preg_replace('/[-_\s]+/', ' ', trim($image_upload['source']['name']));
            /** @var ?Exif */
            $exifRead = $image_upload['source']['image_exif'];
            if ($exifRead instanceof Exif) {
                if (!array_key_exists('title', $params)) {
                    $exifTitle = $exifRead->getTitle();
                    if ($exifTitle !== false) {
                        $title_from_exif = trim($exifTitle);
                        $title_from_exif = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $title_from_exif);
                        if ($title_from_exif !== '') {
                            $image_title = $title_from_exif;
                        }
                    }
                }
                if (!array_key_exists('description', $params)) {
                    $description_from_exif = null;
                    if ($exifRead->getDescription() !== false) {
                        $description_from_exif = trim($exifRead->getDescription());
                    }
                    if ($description_from_exif !== null) {
                        $description_from_exif = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $description_from_exif);
                        $image_insert_values['description'] = $description_from_exif;
                    }
                }
            }
            $image_insert_values['title'] = $image_title;
            if ($fileNaming == 'id' && isset($target_id)) { // Insert as a reserved ID
                $image_insert_values['id'] = $target_id;
            }
            $image_insert_values['title'] = mb_substr($image_insert_values['title'] ?? '', 0, 100, 'UTF-8');
            if ($user !== [] && isset($image_insert_values['album_id'])) {
                $album = Album::getSingle((int) $image_insert_values['album_id']);
                if (($album['user']['id'] ?? 0) != $user['id']) {
                    unset($image_insert_values['album_id'], $album);
                }
            }
            if (isset($ip)) {
                $image_insert_values['uploader_ip'] = $ip;
            }
            $uploaded_id = self::insert($image_upload, $user, $image_insert_values);
            $deletePassword = randomString(48);
            $deleteHash = password_hash($deletePassword, PASSWORD_BCRYPT);
            DB::insert('images_hash', ['image_id' => $uploaded_id, 'hash' => $deleteHash]);
            if (isset($toStorage)) {
                foreach ($toStorage as $k => $v) {
                    unlinkIfExists($v['file']); // Remove files from local when doing external storage
                }
            }
            $privacyTargets = ['private', 'private_but_link'];
            if (in_array($params['privacy'] ?? '', $privacyTargets)
                && (!in_array($album['privacy'] ?? '', $privacyTargets))
            ) {
                $upload_timestamp = $params['timestamp'] ?? time();
                $session_handle = 'upload_' . $upload_timestamp;
                $album = isset(session()[$session_handle])
                    ? Album::getSingle(decodeID(session()[$session_handle]))
                    : null;
                // @phpstan-ignore-next-line
                if (!empty($album) || !in_array($album['privacy'] ?? '', $privacyTargets)) {
                    $inserted_album = Album::insert([
                        'name' => _s('Private upload') . ' ' . datetime('Y-m-d'),
                        'user_id' => $user['id'],
                        'privacy' => $params['privacy']
                    ]);
                    sessionVar()->put($session_handle, $inserted_album);
                    $image_insert_values['album_id'] = $inserted_album;
                } else {
                    // @phpstan-ignore-next-line
                    $image_insert_values['album_id'] = $album['id'];
                }
            }
            if (isset($image_insert_values['album_id'])) {
                Album::addImage($image_insert_values['album_id'], $uploaded_id);
            }
            if ($user !== []) {
                DB::increment('users', ['image_count' => '+1'], ['id' => $user['id']]);
            } elseif ($guestSessionHandle == true) {
                $addValue = session()['guest_images'] ?? [];
                $addValue[] = $uploaded_id;
                sessionVar()->put('guest_images', $addValue);
            }
            if ($switch_to_local) {
                $image_viewer = self::getUrlViewer(encodeID((int) $uploaded_id));
                system_notification_email(['subject' => 'Upload switched to local storage', 'message' => strtr('System has switched to local storage due to not enough disk capacity (%c) in the external storage server(s). The image %s has been allocated to local storage.', ['%c' => $disk_space_needed . ' B', '%s' => '<a href="' . $image_viewer . '">' . $image_viewer . '</a>'])]);
            }

            return [$uploaded_id, $deletePassword];
        } catch (Exception $e) {
            if (isset($image_upload['uploaded'], $image_upload['uploaded']['file'])) {
                unlinkIfExists($image_upload['uploaded']['file']);
            }
            if (isset($image_medium['file'])) {
                unlinkIfExists($image_medium['file']);
            }
            if (isset($image_thumb['file'])) {
                unlinkIfExists($image_thumb['file']);
            }

            throw $e;
        }
    }

    public static function getEnabledImageExtensions(): array
    {
        $formats = explode(',', Settings::get('upload_enabled_image_formats'));
        if (in_array('jpg', $formats) && !in_array('jpeg', $formats)) {
            $formats[] = 'jpeg';
        }
        if (in_array('mov', $formats) && !in_array('quicktime', $formats)) {
            $formats[] = 'quicktime';
        }

        return $formats;
    }

    public static function getEnabledImageAcceptAttribute(): string
    {
        $extensions = self::getEnabledImageExtensions();
        $accept = [];
        $videos = ['mov', 'mp4', 'webm'];
        foreach ($extensions as $extension) {
            $type = in_array($extension, $videos) ? 'video' : 'image';
            if ($extension === 'mov') {
                $extension = 'quicktime';
            }
            $accept[] = "$type/$extension";
        }

        return implode(',', $accept);
    }

    public static function resize(
        string $source,
        ?string $destination,
        ?string $filename = null,
        array $options = []
    ): array {
        $resize = new ImageResize($source);
        $resize->setDestination($destination ?? '');
        if ($filename) {
            $resize->setFilename($filename);
        }
        $resize->setOptions($options);
        if (isset($options['width'])) {
            $resize->setWidth((int) $options['width']);
        }
        if (isset($options['height'])) {
            $resize->setHeight((int) $options['height']);
        }
        if (isset($options['forced']) && $options['forced'] === true) {
            $resize->setOption('forced', true);
        }
        $resize->exec();

        return $resize->resized();
    }

    protected static function insert(array $image_upload, array $user = [], array $values = []): int
    {
        Stat::assertMax('images');
        $table_chv_image = self::$table_chv_image;
        foreach ($table_chv_image as $k => $v) {
            $table_chv_image[$k] = 'image_' . $v;
        }
        if (empty($values['uploader_ip'])) {
            $values['uploader_ip'] = get_client_ip();
        }
        /** @var ?Exif $exifRead */
        $exifRead = $image_upload['source']['image_exif'];
        $exifRaw = null;
        if ($exifRead instanceof Exif) {
            $exifRaw = $exifRead->getRawData();
            unset($exifRaw['MakerNote']);
        }
        $original_exifdata = $exifRaw !== null
            ? json_encode(array_utf8encode($exifRaw))
            : null;
        $values['nsfw'] = in_array(strval($values['nsfw']), ['0', '1']) ? $values['nsfw'] : 0;
        if (Settings::get('moderatecontent')
            && $values['nsfw'] == 0
            && Settings::get('moderatecontent_flag_nsfw')
            && is_object($image_upload['moderation'])
            && property_exists($image_upload['moderation'], 'rating_letter')
        ) {
            switch ($image_upload['moderation']->rating_letter) {
                case 'a':
                    $values['nsfw'] = '1';

                break;
                case 't':
                    if (Settings::get('moderatecontent_flag_nsfw') == 't') {
                        $values['nsfw'] = 1;
                    }

                break;
            }
        }
        $is360 = false;
        if (isset($image_upload['uploaded']['fileinfo']['is_360'])) {
            $is360 = (bool) $image_upload['uploaded']['fileinfo']['is_360'];
        }
        $populate_values = [
            'uploader_ip' => $values['uploader_ip'],
            'md5' => $image_upload['uploaded']['fileinfo']['md5'],
            'original_filename' => $image_upload['source']['filename'],
            'original_exifdata' => $original_exifdata,
            'is_360' => $is360,
            'extension' => $image_upload['uploaded']['extension'],
        ];
        if (!isset($values['date'])) {
            $populate_values = array_merge($populate_values, [
                'date' => datetime(),
                'date_gmt' => datetimegmt(),
            ]);
        }
        $values = array_merge($image_upload['uploaded']['fileinfo'], $populate_values, $values);
        assertNotStopWords(
            $values['name'] ?? '',
            $values['original_filename'] ?? '',
            $values['title'] ?? '',
            $values['description'] ?? ''
        );
        foreach (['title', 'description', 'category_id', 'album_id'] as $v) {
            nullify_string($values[$v]);
        }
        foreach (array_keys($values) as $k) {
            if (!in_array('image_' . $k, $table_chv_image) && $k !== 'id') {
                unset($values[$k]);
            }
        }
        $values['is_approved'] = 1;
        switch (Settings::get('moderate_uploads')) {
            case 'all':
                $values['is_approved'] = (int) (($user['is_admin'] ?? 0) || ($user['is_manager'] ?? 0));

            break;
            case 'guest':
                $values['is_approved'] = (int) isset($values['user_id']);

            break;
        }
        if (Settings::get('moderatecontent_auto_approve')
            && isset($image_upload['moderation'])
        ) {
            $values['is_approved'] = 1;
        }
        $insert = DB::insert('images', $values);
        $disk_space_used = $values['size']
            + $values['thumb_size']
            + $values['medium_size']
            + $values['frame_size'];
        Stat::track([
            'action' => 'insert',
            'table' => 'images',
            'value' => '+1',
            'date_gmt' => $values['date_gmt'],
            'disk_sum' => $disk_space_used,
        ]);
        if (!is_null($values['album_id']) && $insert) {
            Album::updateImageCount((int) $values['album_id'], 1);
        }

        return $insert;
    }

    public static function update(int $id, array $values): int
    {
        $values = array_filter_array($values, self::$table_chv_image, 'exclusion');
        assertNotStopWords($values['title'] ?? '', $values['description'] ?? '');
        foreach (['title', 'description', 'category_id', 'album_id'] as $v) {
            if (!array_key_exists($v, $values)) {
                continue;
            }
            nullify_string($values[$v]);
        }
        if (isset($values['album_id'])) {
            $image_db = self::getSingle($id);
            $old_album = $image_db['image_album_id'];
            $update = DB::update('images', $values, ['id' => $id]);
            if ($update && $old_album !== $values['album_id']) {
                if (!is_null($old_album)) { // Update the old album
                    Album::updateImageCount((int) $old_album, 1, '-');
                }
                Album::updateImageCount((int) $values['album_id'], 1);
            }

            return $update;
        } else {
            return DB::update('images', $values, ['id' => $id]);
        }
    }

    public static function delete(int $id, bool $update_user = true): int
    {
        $image = self::getSingle(id: $id, pretty: true);
        $disk_space_used = $image['size']
            + $image['thumb_size']
            + $image['medium_size']
            + $image['frame_size'];
        if ($image['file_resource']['type'] == 'path') {
            foreach ($image['file_resource']['chain'] as $file_delete) {
                if (file_exists($file_delete) && !unlinkIfExists($file_delete)) {
                    throw new Exception("Can't delete file", 600);
                }
            }
        } else {
            $targets = [];
            foreach ($image['file_resource']['chain'] as $k => $v) {
                $targets[$k] = [
                    'key' => preg_replace('#' . add_ending_slash($image['storage']['url']) . '#', '', $v),
                    'size' => $image[$k]['size'],
                ];
            }
            Storage::deleteFiles($targets, $image['storage']);
        }
        if ($update_user && isset($image['user']['id'])) {
            DB::increment('users', ['image_count' => '-1'], ['id' => $image['user']['id']]);
        }
        if (isset($image['album']['id']) && $image['album']['id'] > 0) {
            Album::updateImageCount((int) $image['album']['id'], 1, '-');
        }
        if (isset($image['album']['cover_id']) && $image['album']['cover_id'] === $image['id']) {
            Album::populateCover((int) $image['album']['id']);
        }
        Stat::track([
            'action' => 'delete',
            'table' => 'images',
            'value' => '-1',
            'date_gmt' => $image['date_gmt'],
            'disk_sum' => $disk_space_used,
            'likes' => $image['likes'],
        ]);
        DB::queryExecute('UPDATE ' . DB::getTable('users') . ' INNER JOIN ' . DB::getTable('likes') . ' ON user_id = like_user_id AND like_content_type = "image" AND like_content_id = ' . $image['id'] . ' SET user_liked = GREATEST(cast(user_liked AS SIGNED) - 1, 0);');
        if (isset($image['user']['id'])) {
            $autoliked = DB::get('likes', ['user_id' => $image['user']['id'], 'content_type' => 'image', 'content_id' => $image['id']])[0] ?? [];
            $likes_counter = (int) $image['likes']; // This is stored as "bigint" but PDO MySQL get it as string. Fuck my code, fuck PHP.
            if ($autoliked !== []) {
                $likes_counter -= 1;
            }
            if ($likes_counter > 0) {
                $likes_counter = 0 - $likes_counter;
            }
            if ($likes_counter !== 0) {
                DB::increment('users', ['likes' => $likes_counter], ['id' => $image['user']['id']]);
            }
            Notification::delete([
                'table' => 'images',
                'image_id' => $image['id'],
                'user_id' => $image['user']['id'],
            ]);
        }
        DB::delete('likes', ['content_type' => 'image', 'content_id' => $image['id']]);
        DB::insert('deletions', [
            'date_gmt' => datetimegmt(),
            'content_id' => $image['id'],
            'content_date_gmt' => $image['date_gmt'],
            'content_user_id' => $image['user']['id'] ?? null,
            'content_ip' => $image['uploader_ip'],
            'content_views' => $image['views'],
            'content_md5' => $image['md5'],
            'content_likes' => $image['likes'],
            'content_original_filename' => $image['original_filename'],
        ]);

        $result = DB::delete('images', ['id' => $id]);
        DB::delete('images_hash', ['image_id' => $id]);

        return $result;
    }

    public static function deleteMultiple(array $ids): int
    {
        $affected = 0;
        foreach ($ids as $id) {
            if (self::delete((int) $id) !== 0) {
                $affected += 1;
            }
        }

        return $affected;
    }

    public static function deleteExpired(int $limit = 50): void
    {
        if (!$limit || !is_numeric($limit)) {
            $limit = 50;
        }
        $db = DB::getInstance();
        $db->query('SELECT image_id FROM ' . DB::getTable('images') . ' WHERE image_expiration_date_gmt IS NOT NULL AND image_expiration_date_gmt < :datetimegmt ORDER BY image_expiration_date_gmt DESC LIMIT ' . $limit . ';'); // Just 50 files per request to prevent CPU meltdown or something like that
        $db->bind(':datetimegmt', datetimegmt());
        $expired_db = $db->fetchAll();
        if ($expired_db) {
            $expired = [];
            foreach ($expired_db as $k => $v) {
                $expired[] = $v['image_id'];
            }
            self::deleteMultiple($expired);
        }
    }

    public static function verifyPassword(int $id, string $password): bool
    {
        $get = DB::get('images_hash', ['image_id' => $id])[0] ?? [];
        if ($get === []) {
            return false;
        }
        $get = DB::formatRow($get, 'image_hash');

        return password_verify($password, $get['hash']);
    }

    public static function fill(array &$image): void
    {
        $image['id_encoded'] = encodeID((int) $image['id']);
        $targets = self::getSrcTargetSingle($image, false);
        $medium_size = getSetting('upload_medium_size');
        $medium_fixed_dimension = getSetting('upload_medium_fixed_dimension');
        if ($targets['type'] == 'path') {
            $is_animated = $image['is_animated'];
            if (!$is_animated) {
                $is_animated = isset($targets['chain']['image']) && is_animated_image($targets['chain']['image']);
            }
            if (count($targets['chain']) > 0) {
                $original_md5 = $image['md5'];
                $image = array_merge($image, get_fileinfo($targets['chain']['image']));
                $image['md5'] = $original_md5;
            }
            if ($is_animated && !$image['is_animated']) {
                self::update($image['id'], ['is_animated' => 1]);
                $image['is_animated'] = 1;
            }
        } else {
            $image_fileinfo = [
                'ratio' => $image['width'] / $image['height'],
                'size' => (int) $image['size'],
                'size_formatted' => format_bytes($image['size'])
            ];

            $image = array_merge($image, get_fileinfo($targets['chain']['image']), $image_fileinfo);
        }
        $image['file_resource'] = $targets;
        $image['url_viewer'] = self::getUrlViewer(
            $image['id_encoded'],
            getSetting('seo_image_urls')
            ? ($image['title'] ?? '')
            : ''
        );
        $image['path_viewer'] = url_to_relative($image['url_viewer']);
        $image['url_short'] = self::getUrlViewer($image['id_encoded']);
        foreach ($targets['chain'] as $k => $v) {
            if ($targets['type'] == 'path') {
                $image[$k] = file_exists($v)
                    ? get_fileinfo($v)
                    : null;
            } else {
                $image[$k] = get_fileinfo($v);
            }
            $image[$k]['size'] = $image[($k == 'image' ? '' : $k . '_') . 'size'];
        }
        $image['url_frame'] = $image['frame']['url'] ?? '';
        $image['size_formatted'] = format_bytes($image['size']);
        $display_url = $image['frame']['url']
            ?? $image['url']
            ?? '';
        $display_width = $image['width'];
        $display_height = $image['height'];
        if (isset($image['medium']['url'])) {
            $display_url = $image['medium']['url'];
            $image_ratio = $image['width'] / $image['height'];
            switch ($medium_fixed_dimension) {
                case 'width':
                    $display_width = $medium_size;
                    $display_height = (int) round($medium_size / $image_ratio);

                break;
                case 'height':
                    $display_height = $medium_size;
                    $display_width = (int) round($medium_size * $image_ratio);

                break;
            }
            $displaySize = $image['medium']['size'];
        } elseif (
            isset($image['thumb']['url'], $image['thumb']['size'])
            && $image['size'] > get_bytes('200 KB')
            && ($image['type'] ?? 1) === 1
        ) {
            $display_url = $image['thumb']['url'];
            $display_width = getSetting('upload_thumb_width');
            $display_height = getSetting('upload_thumb_height');
            $displaySize = $image['thumb']['size'];
        }
        if (isset($image['frame']['size'], $displaySize)
            && $image['frame']['size'] < $displaySize
        ) {
            $display_url = $image['frame']['url'];
            $display_width = $image['width'];
            $display_height = $image['height'];
        }
        $image['duration'] = (int) ($image['duration'] ?? 0);
        $seconds = $image['duration'] ?? 0;
        if ($seconds > 0) {
            $minutes = floor($seconds / 60);
            $duration_time = sprintf('%02d', $minutes) . ':' . sprintf('%02d', $seconds % 60);
        } else {
            $duration_time = '';
        }
        $image['medium'] = $image['medium'] ?? [
            'filename' => null,
            'name' => null,
            'mime' => null,
            'extension' => null,
            'url' => null,
        ];
        $image['thumb'] = $image['thumb'] ?? [
            'filename' => null,
            'name' => null,
            'mime' => null,
            'extension' => null,
            'url' => null,
        ];
        $image['duration_time'] = $duration_time;
        $image['type'] = self::$types[$image['type'] ?? 1];
        $image['display_url'] = $display_url;
        $image['display_width'] = $display_width;
        $image['display_height'] = $display_height;
        $image['views_label'] = _n('view', 'views', $image['views']);
        $image['likes_label'] = _n('like', 'likes', $image['likes']);
        $image['how_long_ago'] = time_elapsed_string($image['date_gmt']);
        $image['date_fixed_peer'] = Login::isLoggedUser()
            ? datetimegmt_convert_tz($image['date_gmt'], Login::getUser()['timezone'])
            : $image['date_gmt'];
        $image['title_truncated'] = truncate($image['title'] ?? '', 28);
        $image['title_truncated_html'] = safe_html($image['title_truncated']);
        $image['is_use_loader'] = getSetting('image_load_max_filesize_mb') !== '' ? ($image['size'] > get_bytes(getSetting('image_load_max_filesize_mb') . 'MB')) : false;
        $image['display_title'] = $image['title']
            ?? ($image['name'] . '.' . $image['extension']);
    }

    public static function formatArray(array $dbRow, bool $safe = false): array
    {
        $output = DB::formatRow($dbRow);
        if (isset($output['user']['id'])) {
            User::fill($output['user']);
        } else {
            unset($output['user']);
        }
        if (isset($output['album']['id']) || isset($output['user']['id'])) {
            $output['user'] = $output['user'] ?? [];
            if (isset($output['album']['password']) && hasEncryption()) {
                try {
                    $output['album']['password'] = decrypt($output['album']['password']);
                } catch (Throwable) {
                    $output['album']['password'] = $output['album']['password'];
                }
            }
            Album::fill($output['album'], $output['user']);
        } else {
            unset($output['album']);
        }
        self::fill($output);
        if ($safe) {
            unset(
                $output['storage'], $output['id'], $output['path'], $output['uploader_ip'],
                $output['album']['id'], $output['album']['privacy_extra'], $output['album']['user_id'],
                $output['album']['password'], $output['album']['cover_id'], $output['album']['parent_id'],
                $output['user']['id'], $output['user']['email'],
                $output['file_resource'],
                $output['file']['resource']['chain'],
            );
        }

        return $output;
    }

    public static function getVideoFrame(string $file, int $time): string
    {
        $frameFile = Upload::getTempNam(sys_get_temp_dir());

        try {
            $ffmpeg = FFMpeg::create(
                [
                    'ffmpeg.binaries' => env()['CHEVERETO_BINARY_FFMPEG'],
                    'ffprobe.binaries' => env()['CHEVERETO_BINARY_FFPROBE'],
                ]
            );
        } catch (Throwable $e) {
            throw new Exception("FFprobe error: " . get_ffmpeg_error($e), 600);
        }

        $video = $ffmpeg->open($file);
        $video
            ->frame(TimeCode::fromSeconds($time))
            ->save($frameFile);

        return $frameFile;
    }
}
