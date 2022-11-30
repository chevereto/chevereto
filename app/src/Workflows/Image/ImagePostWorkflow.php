<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\Image;

use Chevere\Workflow\Interfaces\WorkflowInterface;
use function Chevere\Workflow\job;
use function Chevere\Workflow\workflow;
use Chevereto\Actions\Database\DatabaseReserveRowAction;
use Chevereto\Actions\File\FileFetchSourceAction;
use Chevereto\Actions\File\FileNamingAction;
use Chevereto\Actions\File\FileUploadAction;
use Chevereto\Actions\File\FileValidateAction;
use Chevereto\Actions\File\FileVerifyNotDuplicateAction;
use Chevereto\Actions\Image\ImageFetchMetaAction;
use Chevereto\Actions\Image\ImageFixOrientationAction;
use Chevereto\Actions\Image\ImageInsertAction;
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageVerifyMediaAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Workflow\BaseWorkflow;

final class ImagePostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            fetchSource: job(
                FileFetchSourceAction::class,
                source: '${source}',
            ),
            validateFile: job(
                FileValidateAction::class,
                mimes: '${mimes}',
                filepath: '${fetchSource:filepath}',
                maxBytes: '${max_bytes}',
                minBytes: '${min_bytes}',
            ),
            validateMedia: job(
                ImageVerifyMediaAction::class,
                filepath: '${fetchSource:filepath}',
                maxHeight: '${max_height}',
                maxWidth: '${max_width}',
                minHeight: '${min_height}',
                minWidth: '${min_width}',
            ),
            assertNotDuplicate: job(
                FileVerifyNotDuplicateAction::class,
                md5: '${validateFile:md5}',
                perceptual: '${validateMedia:perceptual}',
                ip: '${ip}',
                ipVersion: '${ip_version}',
            ),
            fixOrientation: job(
                ImageFixOrientationAction::class,
                image: '${validateMedia:image}'
            ),
            fetchMeta: job(
                ImageFetchMetaAction::class,
                image: '${validateMedia:image}'
            ),
            stripMeta: job(
                ImageStripMetaAction::class,
                image: '${validateMedia:image}'
            ),
            storageForUser: job(
                StorageGetForUserAction::class,
                userId: '${user_id}',
                bytesRequired: '${validateFile:bytes}',
            ),
            reserveId: job(
                DatabaseReserveRowAction::class,
                table: '${table}',
            ),
            targetFilename: job(
                FileNamingAction::class,
                id: '${reserveId:id}',
                name: '${name}',
                naming: '${naming}',
                storage: '${storageForUser:storage}',
                path: '${path}'
            ),
            upload: job(
                FileUploadAction::class,
                filepath: '${upload_filepath}',
                targetFilename: '${targetFilename:name}',
                storage: '${storageForUser:storage}',
                path: '${path}',
            ),
            insert: job(
                ImageInsertAction::class,
                id: '${reserveId:id}',
                albumId: '${album_id}',
                expires: '${expires}',
                userId: '${user_id}',
            ),
        );
    }
}
