<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$workingDir = PATH_PUBLIC_CONTENT_LEGACY_THEMES_PEAFOWL_LIB;
$target = 'chevereto-all.js';
$outputFile = $workingDir . $target;
echo "* Compile JavaScript\n";
echo "---\n";
$fh = fopen($outputFile, 'w');
$files = [
    'js/css_browser_detector.js',
    'js/jquery.min.js',
    'js/jquery-ui.min.js',
    'js/hammer.min.js',
    'js/peafowl.js',
    'js/images-loaded.js',
    'js/load-image.js',
    'js/clipboard.js',
    'js/chevereto.js',
];
foreach ($files as $file) {
    $file = $workingDir . $file;
    if (! file_exists($file)) {
        echo "❌ [ERROR] Missing file: {$file}\n";
        exit(1);
    }
    echo "Packing: {$file}\n";
    fwrite($fh, file_get_contents($file) . "\n");
}
fclose($fh);
echo "---\n";
echo "💯 [OK] {$outputFile}\n";
exit(0);
