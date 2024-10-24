<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * This API V1 was introduced in Chevereto V2 and it was carried over to V3.
 *
 * From Chevereto V4 onwards the API versioning follow the major version:
 *
 * - V2 -> API V1
 * - V3 -> API V1
 * - V4 -> API V4 + API V1.1
 */

use Chevereto\Legacy\Classes\Akismet;
use Chevereto\Legacy\Classes\ApiKey;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\Upload;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\G\Handler;
use function Chevereto\Legacy\decodeID;
use function Chevereto\Legacy\encodeID;
use function Chevereto\Legacy\G\get_mimetype;
use function Chevereto\Legacy\G\getQsParams;
use function Chevereto\Legacy\G\is_image_url;
use function Chevereto\Legacy\G\is_url;
use function Chevereto\Legacy\G\json_document_output;
use function Chevereto\Legacy\G\json_error;
use function Chevereto\Legacy\G\mime_to_extension;
use function Chevereto\Legacy\G\random_string;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Vars\env;
use function Chevereto\Vars\files;
use function Chevereto\Vars\request;
use function Chevereto\Vars\server;

return function (Handler $handler) {
    try {
        $user = [];
        $REQUEST = request();
        $FILES = files();
        $SERVER = server();
        $format = $REQUEST['format'] ?? 'json';
        $version = strval($handler->request()[0] ?? '');
        $action = strval($handler->request()[1] ?? '');
        $key = strval($SERVER['HTTP_X_API_KEY'] ?? $REQUEST['key'] ?? '');
        foreach (['version', 'action', 'key'] as $var) {
            if (${$var} === '') {
                throw new Exception("No {$var} provided", 100);
            }
        }
        if (! in_array($version, ['1'], true)) {
            throw new Exception('Invalid API version.', 110);
        }
        $verify = ApiKey::verify($key);
        if ($verify === []) {
            if (! (bool) env()['CHEVERETO_ENABLE_API_GUEST']) {
                throw new Exception('Invalid API key.', 100);
            }
            $apiV1Key = (string) (getSetting('api_v1_key') ?? '');
            if ($apiV1Key === '') {
                throw new Exception("API V1 public key can't be null. Go to /dashboard and set the Guest API key.", 0);
            }
            if (! hash_equals($apiV1Key, $key)) {
                throw new Exception('Invalid API key.', 100);
            }
        } else {
            $user = User::getSingle($verify['user_id']);
        }
        $isAdmin = boolval(($user['is_admin'] ?? false));
        if (Settings::get('enable_uploads_url') && ! $isAdmin) {
            Settings::setValue('enable_uploads_url', 0);
        }
        if (! $handler::cond('upload_allowed')) {
            throw new Exception(_s('Request denied'), 401);
        }
        $version_to_actions = [
            '1' => ['upload'],
        ];
        if (! in_array($action, $version_to_actions[$version], true)) {
            throw new Exception('Invalid API action', 120);
        }
        $source = $FILES['source']
            ?? $REQUEST['source']
            ?? $REQUEST['image']
            ?? null;
        if ($source === null) {
            throw new Exception('Empty upload source', 130);
        }
        switch (true) {
            case isset($FILES['source'], $FILES['source']['tmp_name']):
                $source = $FILES['source'];

                break;
            case is_image_url($source) || is_url($source):
                if (($SERVER['REQUEST_METHOD'] ?? '') === 'GET') {
                    $sourceQs = urldecode(getQsParams()['source']);
                }
                $source = $sourceQs ?? $source;

                break;
            default:
                if (($SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
                    throw new Exception('Upload using base64 source must be done using POST method.', 130);
                }
                $source = trim(preg_replace('/\s+/', '', $source));
                $base64source = base64_encode(base64_decode($source, true));
                if (! hash_equals($base64source, $source)) {
                    throw new Exception('Invalid base64 string', 120);
                }
                if ($source === '') {
                    throw new Exception('Empty source', 130);
                }

                try {
                    $api_temp_file = Upload::getTempNam();
                } catch (Exception $e) {
                    throw new Exception("Can't get a tempnam", 200);
                }
                $fh = fopen($api_temp_file, 'w');
                stream_filter_append($fh, 'convert.base64-decode', STREAM_FILTER_WRITE);
                fwrite($fh, $source);
                fclose($fh);
                $mimetype = get_mimetype($api_temp_file);
                $source = [
                    'name' => random_string(12) . '.' . mime_to_extension($mimetype),
                    'type' => $mimetype,
                    'tmp_name' => $api_temp_file,
                    'error' => 'UPLOAD_ERR_OK',
                    'size' => filesize($api_temp_file),
                ];

                break;
        }
        $isImgBBSpec = array_key_exists('image', $REQUEST);
        $albumId = $REQUEST['album_id'] ?? null;
        if ($albumId !== null) {
            $albumId = decodeID($albumId);
        }
        $expiration = $REQUEST['expiration'] ?? null;
        if ($expiration !== null && ctype_digit($expiration)) {
            $expiration = (int) $expiration;
        }
        $params = [
            'album_id' => $albumId,
            'category_id' => $REQUEST['category_id'] ?? null,
            'description' => $REQUEST['description'] ?? null,
            'nsfw' => $REQUEST['nsfw'] ?? null,
            'title' => $REQUEST['title'] ?? $REQUEST['name'] ?? null,
            'tags' => $REQUEST['tags'] ?? null,
            'width' => $REQUEST['width'] ?? null,
            'expiration' => $expiration,
        ];
        $params = array_filter($params);
        if (! $handler::cond('content_manager') && getSetting('akismet')) {
            $user_source_db = [
                'user_name' => $user['name'] ?? null,
                'user_username' => $user['username'] ?? null,
                'user_email' => $user['email'] ?? null,
            ];
            Akismet::checkImage($params['title'] ?? null, $params['description'] ?? null, $user_source_db);
        }
        $uploadToWebsite = Image::uploadToWebsite($source, $user, $params);
        $uploaded_id = intval($uploadToWebsite[0]);
        $image = Image::formatArray(Image::getSingle($uploaded_id), true);
        $image['delete_url'] = Image::getDeleteUrl(
            type: $image['type'],
            idEncoded: encodeID($uploaded_id),
            password: $uploadToWebsite[1]
        );
        unset($image['user'], $image['album']);
        if (! $image['is_approved']) {
            unset($image['image']['url'], $image['thumb']['url'], $image['medium']['url'], $image['url'], $image['display_url']);
        }
        $json_array = [];
        $json_array['status_code'] = 200;
        if ($isImgBBSpec) {
            $json_array['status'] = $json_array['status_code'];
            $image['id'] = $image['id_encoded'];
        }
        $json_array['success'] = [
            'message' => 'file uploaded',
            'code' => 200,
        ];
        $json_array[$isImgBBSpec ? 'data' : 'image'] = $image;

        if ($version === '1') {
            switch ($format) {
                default:
                case 'json':
                    json_document_output($json_array);

                    break;
                case 'txt':
                    echo $image['url'];

                    break;
                case 'redirect':
                    if ($json_array['status_code'] === 200) {
                        $redirect_url = $image['path_viewer'];
                        header("Location: {$redirect_url}");
                    } else {
                        exit($json_array['status_code']);
                    }

                    break;
            }
            exit();
        }
        json_document_output($json_array);
    } catch (Exception $e) {
        $json_array = json_error($e);
        if ($version === '1') {
            switch ($format) {
                default:
                case 'json':
                    json_document_output($json_array);

                    break;
                case 'txt':
                case 'redirect':
                    exit($json_array['error']['message']);
            }
        } else {
            json_document_output($json_array);
        }
    }
};
