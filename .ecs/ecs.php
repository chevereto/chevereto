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
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Option;

return static function (ECSConfig $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/ecs-chevere.php');
    $services = $containerConfigurator->services();
    $services->remove(DeclareStrictTypesFixer::class);
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SKIP, []);
};
