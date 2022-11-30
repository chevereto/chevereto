<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\User\Asset\Background;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageVerifyMediaAction;
use Chevereto\Actions\Storage\StorageGetForAssetAction;
use Chevereto\Workflow\BaseWorkflow;
use function Chevereto\Workflow\stepVerifyResourceAccess;

final class UserAssetBackgroundPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            user: job(
                'UserGetByUsernameAction',
                username: '${username}',
            ),
            checkout: stepVerifyResourceAccess(
                resource: 'user_background',
                level: 'write',
                ownerUserId: '${user:id}'
            ),
            fetchSource: job(
                FileFetchBinaryAction::class,
                source: '${source}',
            ),
            validateFile: job(
                FileValidateAction::class,
                filepath: '${fetchSource:uploadFilepath}',
                maxBytes: '${user_background_max_bytes}',
                mimes: '${user_background_mimes}',
                minBytes: '${user_background_min_bytes}',
            ),
            validateMedia: job(
                ImageVerifyMediaAction::class,
                filepath: '${fetchSource:uploadFilepath}',
                maxHeight: '${user_background_max_height}',
                maxWidth: '${user_background_max_width}',
                minHeight: '${user_background_min_height}',
                minWidth: '${user_background_min_width}',
            ),
            fixOrientation: job(
                ImageFixOrientationAction::class,
                image: '${validateMedia:image}'
            ),
            stripMeta: job(
                ImageStripMetaAction::class,
                image: '${validateMedia:image}'
            ),
            storageForAsset: job(
                StorageGetForAssetAction::class,
                bytesRequired: '${validateFile:bytes}',
                userId: '${user_id}',
            ),
            upload: job(
                FileUploadAction::class,
                filepath: '${upload_filepath}',
                path: '${asset:path}',
                storage: '${storageForAsset:storage}',
                targetFilename: '${asset:filename}',
            )
        );
    }
}
