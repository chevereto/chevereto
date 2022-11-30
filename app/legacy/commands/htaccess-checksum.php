<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use function Chevereto\Legacy\G\forward_slash;
use function Chevereto\Legacy\G\rrmdir;
use function Chevereto\Legacy\getSetting;
use function Safe\file_get_contents;
use function Safe\mkdir;

echo "* Checksum Apache HTTP Web Server .htaccess files\n";
$apacheDir = PATH_APP . 'apache/';
$checksumFile = $apacheDir . 'checksums.php';
rrmdir($apacheDir);
mkdir($apacheDir);
$files = glob(PATH_PUBLIC . "{*/,*/*/,*/*/*/}.htaccess", GLOB_BRACE);
$noPhpHtaccess = PATH_PUBLIC . 'content/.htaccess';
$denyHtaccess = PATH_PUBLIC . 'app/.htaccess';
$imagesHtaccess = PATH_PUBLIC . (getSetting('upload_image_path') ?? 'images')
    . '/.htaccess';
$importingHtaccess = PATH_PUBLIC . 'importing/.htaccess';
if (!file_exists($imagesHtaccess)) {
    $files[] = $imagesHtaccess;
    file_put_contents($imagesHtaccess, file_get_contents($noPhpHtaccess));
}
if (!file_exists($importingHtaccess)) {
    $files[] = $importingHtaccess;
    file_put_contents($importingHtaccess, file_get_contents($denyHtaccess));
}
$files = array_unique($files);
$checksums = [];
foreach ($files as $file) {
    $relativeFile = str_replace(PATH_PUBLIC, '', forward_slash($file));
    $md5File = md5_file($file);
    file_put_contents($apacheDir . $md5File, file_get_contents($file));
    $checksums[$relativeFile] = $md5File;
    echo '  - ' . $relativeFile . ' > ' . $md5File . "\n";
}
file_put_contents(
    $checksumFile,
    '<?php return '
    . var_export($checksums, true)
    . ';'
);
echo '✅ [DONE] Checksums stored at ' . $checksumFile . "\n";
die(0);
