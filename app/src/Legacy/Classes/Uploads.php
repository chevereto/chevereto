<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Chevereto\Legacy\Classes;

use Exception;
use PDO;
use function Chevereto\Legacy\getVariable;

final class Uploads
{
    /**
     * @return array The file path, and the upload params
     */
    public static function join(
        string $uploadPath,
        int $uploadId,
        string $token,
        string $hash
    ): array {
        $calcHash = hash_hmac(
            'sha256',
            $uploadId . $token,
            getVariable('crypt_salt')->string()
        );
        if (! hash_equals($calcHash, $hash)) {
            throw new Exception('Invalid hash', 100);
        }
        $uploadsTable = DB::getTable('uploads');
        $uploadChunksTable = DB::getTable('uploads_chunks');
        $uploadSQL = <<<SQL
        SELECT `upload_params` `params`
        FROM `{$uploadsTable}`
        WHERE upload_id = :upload_id
            AND `upload_token` = :token
            AND `upload_completed` = 0;
        SQL;
        $chunksSQL = <<<SQL
        SELECT `upload_chunk_index` `index`, `upload_chunk_path` path
        FROM `{$uploadChunksTable}`
        WHERE `upload_chunk_upload_id` = :upload_id
        ORDER BY `upload_chunk_index` ASC;
        SQL;
        $db = DB::getInstance();
        $upload = $db->query($uploadSQL);
        $db->bind(':upload_id', $uploadId);
        $db->bind(':token', $token);
        $upload = $db->fetchSingle();
        if ($upload === false) {
            throw new Exception('Invalid upload', 403);
        }
        $params = json_decode($upload['params'], true);
        $chunks = $db->query($chunksSQL);
        $db->bind(':upload_id', $uploadId);
        $chunks = $db->fetchAll(PDO::FETCH_KEY_PAIR);
        if ($chunks === false) {
            throw new Exception('Missing chunked map', 403);
        }
        $tempName = Upload::getTempNam($uploadPath);
        $chunkedFile = fopen($tempName, 'w');
        if ($chunkedFile === false) {
            throw new Exception('Unable to open chunked file', 600);
        }
        foreach ($chunks as $chunkPath) {
            if (! is_file($chunkPath)) {
                throw new Exception('Missing chunk', 403);
            }
            $chunkFile = fopen($chunkPath, 'rb');
            if (! $chunkFile) {
                throw new Exception('Unable to open chunk', 600);
            }
            if (stream_copy_to_stream($chunkFile, $chunkedFile) === false) {
                fclose($chunkFile);

                throw new Exception('Failed copying chunk', 600);
            }
            fclose($chunkFile);
            unlink($chunkPath);
        } // slow: 4s
        fclose($chunkedFile);
        DB::delete('uploads', [
            'id' => $uploadId,
        ]);

        return [$tempName, $params];
    }
}
