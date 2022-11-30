<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Workflows\Legacy;

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
use Chevereto\Actions\Image\ImageStripMetaAction;
use Chevereto\Actions\Image\ImageVerifyMediaAction;
use Chevereto\Actions\Legacy\Api\V1\ImageInsertAction;
use Chevereto\Actions\Legacy\Api\V1\LegacyApiV1OutputAction;
use Chevereto\Actions\Legacy\Api\V1\LegacyApiV1VerifyKeyAction;
use Chevereto\Actions\Storage\StorageGetForUserAction;
use Chevereto\Workflow\BaseWorkflow;

final class LegacyUploadPostWorkflow extends BaseWorkflow
{
    public function getWorkflow(): WorkflowInterface
    {
        return workflow(
            validateApiV1Key: job(
                LegacyApiV1VerifyKeyAction::class,
                key: '${key}',
                apiV1Key: '${api_v1_key}',
            ),
            fetchSource: job(
                FileFetchSourceAction::class,
                source: '${source}',
            ),
            validateFile: job(
                FileValidateAction::class,
                mimes: '${api_v1_upload_mimes}',
                filepath: '${fetchSource:filepath}',
                maxBytes: '${api_v1_upload_max_bytes}',
                minBytes: '${api_v1_upload_min_bytes}',
            ),
            validateMedia: job(
                ImageVerifyMediaAction::class,
                filepath: '${fetchSource:filepath}',
                maxHeight: '${api_v1_upload_max_height}',
                maxWidth: '${api_v1_upload_max_width}',
                minHeight: '${api_v1_upload_min_height}',
                minWidth: '${api_v1_upload_min_width}',
            ),
            assertNotDuplicate: job(
                FileVerifyNotDuplicateAction::class,
                md5: '${validateFile:md5}',
                perceptual: '${validateMedia:perceptual}',
                ip: '${requester_ip}',
                ipVersion: '${requester_ip_version}',
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
                table: '${table_image}',
            ),
            targetFilename: job(
                FileNamingAction::class,
                id: '${reserveId:id}',
                name: '${name}',
                naming: '${naming}',
                storage: '${storageForUser:storage}',
                path: '${api_v1_upload_path}'
            ),
            upload: job(
                FileUploadAction::class,
                filepath: '${fetchSource:filepath}',
                targetFilename: '${targetFilename:filename}',
                storage: '${storageForUser:storage}',
                path: '${api_v1_upload_path}',
            ),
            insert: job(
                ImageInsertAction::class,
                id: '${reserveId:id}',
            ),
            output: job(
                LegacyApiV1OutputAction::class,
                format: '${format}',
            )
        );
    }
}
