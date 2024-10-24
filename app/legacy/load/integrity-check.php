<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy;

use Chevereto\Config\Config;
use Chevereto\Config\SystemConfig;
use Imagick;
use Throwable;
use function Chevereto\Legacy\G\absolute_to_relative;
use function Chevereto\Legacy\G\is_writable;
use function Chevereto\Vars\server;

function getFailingImageFormats(string $imageLibrary): array
{
    $mustHaveFormats = ['PNG', 'GIF', 'JPEG', 'BMP', 'WEBP'];
    $image_formats_available = Config::system()->imageFormatsAvailable();
    if (is_array($image_formats_available)) {
        $mustHaveFormats = $image_formats_available;
    }
    $failed_formats = [];
    if ($imageLibrary === 'imagick') {
        $imageFormats = Imagick::queryFormats();
        foreach ($mustHaveFormats as $format) {
            if (! in_array($format, $imageFormats)) {
                $failed_formats[] = $format;
            }
        }
    } elseif ($imageLibrary === 'gd') {
        $imageTypes = imagetypes();
        foreach ($mustHaveFormats as $format) {
            if (! ($imageTypes & constant("IMG_{$format}"))) {
                $failed_formats[] = $format;
            }
        }
    }

    return array_map('strtolower', $failed_formats);
}

function filesystemPermissionsCheck(): void
{
    $errors = [];
    $writing_paths = [CHV_PATH_IMAGES, PATH_PUBLIC_CONTENT, PATH_APP_CONTENT];
    foreach ($writing_paths as $v) {
        if (! file_exists($v)) {
            try {
                mkdir($v);
            } catch (Throwable) {
                $errors[] = '<code>'
                    . absolute_to_relative($v)
                    . "</code> doesn't exists and can't be created.";
            }
        } else {
            if (! is_writable($v)) {
                $errors[] = 'No write permission for PHP user '
                    . get_current_user()
                    . ' in <code>'
                    . absolute_to_relative($v)
                    . '</code> directory. Chevereto needs to be able to write in this directory.';
            }
        }
    }
    dieChecksErrors($errors);
}

function phpCheck(SystemConfig $systemConfig): void
{
    $errors = [];
    $missing_tpl = '%n (<a href="http://php.net/manual/en/%t.%u.php" target="_blank">%f</a>) %t is disabled in this server. This %t must be enabled in your PHP configuration (php.ini) and/or you must add this missing %t.';
    if (version_compare(PHP_VERSION, '8.0.0', '<')) {
        $errors[] = 'This server is currently running PHP version '
            . PHP_VERSION
            . ' and Chevereto needs at least PHP 8.0.0 to run.';
    }
    if (ini_get('allow_url_fopen') !== '1' && ! function_exists('curl_init')) {
        $errors[] = "cURL isn't installed and allow_url_fopen is disabled. Chevereto needs one of these to perform HTTP requests to remote servers.";
    }
    if (preg_match('/apache/i', server()['SERVER_SOFTWARE'] ?? '')
        && function_exists('apache_get_modules')
        && ! in_array('mod_rewrite', apache_get_modules())
    ) {
        $errors[] = 'Apache <a href="http://httpd.apache.org/docs/2.1/rewrite/rewrite_intro.html" target="_blank">mod_rewrite</a> is not enabled in this server. This must be enabled to run Chevereto.';
    }
    $extensionsRequired = [
        'exif' => [
            '%label' => 'Exif',
            '%name' => 'Exchangeable image information',
            '%slug' => 'book.exif',
            '%desc' => 'Exif is required to handle image metadata',
        ],
        'pdo' => [
            '%label' => 'PDO',
            '%name' => 'PHP Data Objects',
            '%slug' => 'book.pdo',
            '%desc' => 'PDO is needed to perform database operations',
        ],
        'pdo_mysql' => [
            '%label' => 'PDO_MYSQL',
            '%name' => 'PDO MySQL Functions',
            '%slug' => 'ref.pdo-mysql',
            '%desc' => 'PDO_MYSQL is needed to work with a MySQL database',
        ],
        'fileinfo' => [
            '%label' => 'fileinfo',
            '%name' => 'Fileinfo',
            '%slug' => 'book.fileinfo',
            '%desc' => 'Fileinfo is required for file handling',
        ],
    ];
    $php_image = [
        'imagick' => [
            '%label' => 'imagick',
            '%name' => 'Imagick',
            '%slug' => 'book.imagick',
            '%desc' => 'Imagick is needed for image processing',
        ],
        'gd' => [
            '%label' => 'gd',
            '%name' => 'gd',
            '%slug' => 'book.gd',
            '%desc' => 'GD is needed for image processing',
        ],
    ];
    $imageLibs = [
        'gd' => extension_loaded('gd') && function_exists('gd_info'),
        'imagick' => extension_loaded('imagick'),
    ];
    $systemConfigLib = $systemConfig->imageLibrary();
    if ($systemConfigLib === '') {
        $errors[] = 'No image handling library in this server. Enable either Imagick extension or GD extension to perform image processing.';
    } else {
        $extensionsRequired[$systemConfigLib] = $php_image[$systemConfigLib] ?? [];
    }

    if (($imageLibs[$systemConfigLib] ?? null) === false) {
        $errors[] = 'Configured image_library ' . $systemConfigLib . ' is not present in this system.';
    }
    foreach ($extensionsRequired as $k => $v) {
        if (! extension_loaded($k)) {
            $errors[] = strtr('%name (<a href="http://www.php.net/manual/%slug.php">%label</a>) is not loaded in this server. %desc.', $v);
        }
    }
    $disabled_classes = explode(',', preg_replace('/\s+/', '', ini_get('disable_classes')));
    if ($disabled_classes !== []) {
        foreach (['DirectoryIterator', 'RegexIterator', 'Pdo', 'Exception'] as $k) {
            if (in_array($k, $disabled_classes)) {
                $errors[] = strtr(str_replace('%t', 'class', $missing_tpl), [
                    '%n' => $k,
                    '%f' => $k,
                    '%u' => str_replace('_', '-', strtolower($k)),
                ]);
            }
        }
    }
    dieChecksErrors($errors);
}

function dieChecksErrors(array $errors): void
{
    if ($errors === []) {
        return;
    }
    if (PHP_SAPI === 'cli') {
        vdd($errors);
    }
    chevereto_die($errors);
}
