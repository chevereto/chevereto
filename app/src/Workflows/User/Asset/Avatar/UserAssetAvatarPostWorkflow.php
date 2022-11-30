<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\User\Asset\Avatar;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Actions\File\FileFetchSourceAction;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageVerifyMediaAction;
use Chevereto\Actions\Storage\StorageGetForAssetAction;
use Chevereto\Workflow\BaseWorkflow;
use function Chevereto\Workflow\stepVerifyResourceAccess;

final class UserAssetAvatarPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            user: job(
                'UserGetByUsernameAction',
                username: '${username}',
            ),
            checkout: stepVerifyResourceAccess(
                resource: 'user_avatar',
                level: 'write',
                ownerUserId: '${user:id}'
            ),
            fetchSource: job(
                FileFetchSourceAction::class,
                source: '${source}',
            ),
            validateFile: job(
                FileValidateAction::class,
                mimes: '${user_avatar_mimes}',
                filepath: '${fetch_source:filepath}',
                maxBytes: '${user_avatar_max_bytes}',
                minBytes: '${user_avatar_min_bytes}',
            ),
            validateMedia: job(
                ImageVerifyMediaAction::class,
                filepath: '${fetch_source:filepath}',
                maxHeight: '${user_avatar_max_height}',
                maxWidth: '${user_avatar_max_width}',
                minHeight: '${user_avatar_min_height}',
                minWidth: '${user_avatar_min_width}',
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
                userId: '${user_id}',
                bytesRequired: '${validate_file:bytes}',
            ),
            upload: job(
                FileUploadAction::class,
                filepath: '${upload_filepath}',
                targetFilename: '${asset:filename}',
                storage: '${storage_for_asset:storage}',
                path: '${asset:path}',
            )
        );
    }
}
