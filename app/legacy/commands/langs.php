<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\G\Gettext;
use function Chevere\Filesystem\directoryForPath;
use function Chevereto\Legacy\G\unlinkIfExists;
use function Chevereto\Legacy\get_available_languages;

echo "* Re-cache all languages\n";
echo "---\n";
L10n::cacheFilesystemLocales();
$languages = array_keys(get_available_languages());
directoryForPath(L10n::PATH_CACHE)->createIfNotExists();
directoryForPath(L10n::PATH_CACHE_OVERRIDES)->createIfNotExists();
foreach ($languages as $lang) {
    $filename = $lang . '.po';
    $language_file = PATH_APP_LANGUAGES . $filename;
    $language_override_file = PATH_APP_LANGUAGES . 'overrides/' . $filename;
    $language_handling = [
        'base' => [
            'file' => $language_file,
            'cache_path' => L10n::PATH_CACHE,
            'table' => [],
        ],
        'override' => [
            'file' => $language_override_file,
            'cache_path' => L10n::PATH_CACHE_OVERRIDES,
            'table' => [],
        ],
    ];
    foreach ($language_handling as $k => $v) {
        $cache_path = $v['cache_path'];
        $cache_file = basename($v['file']) . '.cache.php';
        if (! file_exists($v['file'])) {
            continue;
        }
        $cache = $cache_path . $cache_file;
        unlinkIfExists($cache);
        new Gettext([
            'file' => $v['file'],
            'cache_filepath' => $cache,
            'cache_header' => $k == 'base',
        ]);
    }
    echo "{$lang}\n";
    if (file_exists($language_override_file)) {
        echo "{$lang} [override]\n";
    }
}
echo "---\n";
echo L10n::LOCALES_AVAILABLE_FILEPATH . "\n";
echo "💯 [OK] Languages re-cached\n";
exit(0);
