<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Controllers\Api\V4\User\Asset\Background;

use Chevere\Controller\Attributes\RelationWorkflow;
use Chevere\Parameter\Interfaces\ParametersInterface;
use function Chevere\Parameter\objectParameter;
use function Chevere\Parameter\parameters;
use Chevereto\Controllers\Api\V4\File\FilePostController;
use Chevereto\Workflows\User\Asset\Background\UserAssetBackgroundPostWorkflow;

#[RelationWorkflow(UserAssetBackgroundPostWorkflow::class)]
final class UserAssetBackgroundPostController extends FilePostController
{
    public function getDescription(): string
    {
        return 'Uploads an image resource to be used as user background.';
    }

    public function getResponseParameters(): ParametersInterface
    {
        return
            parameters(
                file_info: objectParameter(
                    className: FileInfo::class
                )
            );
    }
}
