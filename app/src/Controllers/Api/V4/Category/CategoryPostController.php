<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\Category;

use Chevere\Parameter\Attributes\ParameterAttribute;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\WorkflowController;

final class CategoryPostController extends WorkflowController
{
    public function getDescription(): string
    {
        return 'Creates a category.';
    }

    public function run(
        string $name,
        #[ParameterAttribute(
            description: 'Category URL key (slug)',
            regex: '/^[-\w]+$/',
        )]
        string $url_key,
        string $description
    ): array {
        return [];
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                category: objectParameter(
                    className: Category::class
                ),
            );
    }
}
