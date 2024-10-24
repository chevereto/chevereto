<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;

return static function (ECSConfig $ecsConfig): void {
    $ecsConfig->import(__DIR__ . '/ecs-chevere.php');
    $ecsConfig->skip([
        DeclareStrictTypesFixer::class,
        StrictComparisonFixer::class,
        getcwd() . '/vendor/*',
        getcwd() . '/content/legacy/themes/Peafowl/',
    ]);
};
