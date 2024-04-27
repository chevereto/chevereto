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

Download (auto license):
    php app/upgrading.php

Download (with license):
    CHEVERETO_LICENSE_KEY=your_license_key php app/upgrading.php

* .upgrading/upgrading.lock
This setting affects non CLI (HTTP calls only).
It exists when the upgrade has been authorized at dashboard.
It contains the token for upgrade process, must be checked against request.

* .upgrading/downloading.lock
It exists when the upgrade is downloading the new version.

* .upgrading/extracting.lock
It exists when the upgrade is extracting the new version.

*/
namespace Chevereto;

use Exception;
use RuntimeException;
use stdClass;
use Throwable;
use ZipArchive;

require_once __DIR__ . '/legacy/load/php-boot.php';

const ZIP_BALL = 'https://chevereto.com/api/download/%tag%';
const LOGGER = __DIR__ . '/.upgrading/process.log';
if (!file_exists(LOGGER)) {
    touch(LOGGER);
}
ob_start('ob_gzhandler');
ob_implicit_flush(true);
$rootDir = __DIR__ . '/..';
$workingDir = __DIR__ . '/.upgrading';
if (is_file($workingDir)) {
    unlink($workingDir);
}
ini_set('log_errors', true);
ini_set('display_errors', true);
ini_set('error_log', $workingDir . '/error.log');
ignore_user_abort(true);
@set_time_limit(0);
ini_set('default_charset', 'utf-8');
setlocale(LC_ALL, 'en_US.UTF8');
ini_set('output_buffering', 'off');
ini_set('zlib.output_compression', false);
$logProcess = $workingDir . '/process.log';
$lockUpgrading = $workingDir . '/upgrading.lock';
$lockDownloading = $workingDir . '/downloading.lock';
$lockExtracting = $workingDir . '/extracting.lock';
$upgradingKey = $rootDir . '/app/CHEVERETO_LICENSE_KEY';
if (PHP_SAPI !== 'cli') {
    header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
    echo <<<HTML
    <html><head><style>body {padding: 0.5em;}</style><script>
    function goToUrl(url) {
        window.location.href = url;
    }
    </script></head><body><pre>
    HTML;
}
if (!is_dir($workingDir)) {
    mkdir($workingDir, 0755, true);
}
if (!is_writable($workingDir)) {
    abort('[!] Working dir is not writable', 500);
}
$envFile = __DIR__ . '/env.php';
$env = [];
if (file_exists($envFile)) {
    $env = require $envFile;
}
$env = array_merge($_ENV, $_SERVER, $env);
if (!class_exists('ZipArchive')) {
    abort('[!] ZipArchive is not available');
}
$licenseKey = $env['CHEVERETO_LICENSE_KEY'] ?? '';
if ($licenseKey === '' && file_exists($upgradingKey)) {
    $licenseKey = file_get_contents($upgradingKey);
}
$return = $_GET['return'] ?? '';
$parseUri = parse_url($_SERVER['REQUEST_URI'] ?? '');
$query = $parseUri['query'] ?? '';
$pathUrl = $parseUri['path'] ?? '';
$rootUrl = rtrim(dirname($pathUrl), '/') . '/';
$actions = ['download', 'extract'];
$filePath = $workingDir . '/' . 'chevereto.zip';
if (PHP_SAPI === 'cli') {
    echo <<<LOGO
          __                        __
     ____/ /  ___ _  _____ _______ / /____
    / __/ _ \/ -_) |/ / -_) __/ -_) __/ _ \
    \__/_//_/\__/|___/\__/_/  \__/\__/\___/


    LOGO;
    $singleStep = true;
    $clear = getopt('c::') ?? null;
    if ($clear) {
        unlinkIfExists($lockUpgrading);
        unlinkIfExists($lockDownloading);
        unlinkIfExists($lockExtracting);
        logger('Locks cleared');
        die(0);
    }
} else {
    $singleStep = false;
    $action = (string) ($_GET['action'] ?? '');
    $token = (string) ($_GET['token'] ?? '');
    if (!file_exists($lockUpgrading)) {
        abort('[!] Upgrade is not expected', 403);
    }
    $upgradeToken = file_get_contents($lockUpgrading);
    if ($upgradeToken === false) {
        abort('[!] Invalid token file', 403);
    }
    if (!hash_equals($upgradeToken, $token)) {
        abort('[!] Invalid token', 403);
    }
    if (($env['CHEVERETO_CONTEXT'] ?? null) === 'saas') {
        abort('[!] Upgrade is not needed on SaaS context', 403);
    }
    if (!in_array($action, $actions, true)) {
        abort('[!] Provide action=download or action=extract', 400);
    }
}
$upgradeToken ??= time();
if ($singleStep || $action === 'download') {
    if (file_exists($lockDownloading)) {
        abort('[!] Downloading is already in progress', 400);
    }
    logger('Lock downloading process');
    file_put_contents($lockDownloading, $upgradeToken);
    $params['tag'] = '4';
    $params['license'] = $licenseKey;
    if ($params['license'] === '') {
        logger('Using free version [no CHEVERETO_LICENSE_KEY provided]');
    } else {
        logger('Attempt to use licensed version [CHEVERETO_LICENSE_KEY provided]');
    }
    logger(sprintf('About to download Chevereto %s', $params['tag']));

    try {
        $response = downloadAction($workingDir, $params);
    } catch (Throwable $e) {
        logger('Unlock downloading process');
        unlink($lockDownloading);
        abort($e->getMessage(), 400);
    }
    logger($response->message);
    logger('Unlock downloading process');
    unlink($lockDownloading);
    $query = str_replace('action=download', 'action=extract', $query);
    if (PHP_SAPI !== 'cli') {
        $continueUri = $pathUrl . '?' . $query;
        logger('Continue extraction in 3s at... ' . $continueUri);
        sleep(3);
    }
}
if ($singleStep || $action === 'extract') {
    if (PHP_SAPI !== 'cli') {
        echo file_get_contents(LOGGER);
    }
    if (file_exists($lockExtracting)) {
        abort('[!] Extracting is already in progress', 400);
    }
    if (!file_exists($filePath)) {
        abort('[!] Package not downloaded', 400);
    }
    logger('Lock extracting process');
    file_put_contents($lockExtracting, $upgradeToken);

    try {
        $response = extractAction($rootDir, $filePath);
    } catch (Throwable $e) {
        logger('Unlock extracting process');
        unlink($lockExtracting);
        abort($e->getMessage(), $e->getCode());
    }
    logger($response->message);
    unlink($filePath);
    logger('Unlock extracting process');
    unlink($lockExtracting);
    logger('Chevereto filesystem upgraded');
    unlinkIfExists($lockUpgrading);
    $safeResult = false;
    $command = $rootDir . '/app/bin/legacy -C update';
    if (passthruEnabled()) {
        logger('Command passthru');
        $safeResult = passthru($command);
    }
    if ($safeResult === false) {
        logger('Continue with database update');
    }
    if (PHP_SAPI !== 'cli') {
        $continueUri = $rootUrl . $return;
        logger('Redirecting in 3s...');
        sleep(3);
    }
    unlink(LOGGER);
}
if (PHP_SAPI !== 'cli') {
    echo '</pre></body>';
    if (isset($continueUri)) {
        echo <<<HTML
        <script>goToUrl("{$continueUri}")</script>
        HTML;
    }
    echo '</html>';
}

function logger(string $message): void
{
    $hour = gmdate('H:i:s');
    $message = $hour . ' * ' . $message . PHP_EOL;
    fwrite(fopen('php://output', 'r+'), $message);
    fwrite(fopen(LOGGER, 'a+'), $message);
    ob_flush();
}

function curl(string $url, array $curlOpts = []): object
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 60);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_FAILONERROR, 0);
    curl_setopt($ch, CURLOPT_VERBOSE, 0);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Chevereto Upgrade');
    $fp = false;
    foreach ($curlOpts as $k => $v) {
        if (CURLOPT_FILE == $k) {
            $fp = $v;
        }
        curl_setopt($ch, $k, $v);
    }
    $file_get_contents = curl_exec($ch);
    $transfer = curl_getinfo($ch);
    if (curl_errno($ch)) {
        $curl_error = curl_error($ch);
        curl_close($ch);

        throw new Exception('Curl error ' . $curl_error, 500);
    }
    curl_close($ch);
    $return = new stdClass();
    if (is_resource($fp)) {
        rewind($fp);
        $return->raw = stream_get_contents($fp);
    } else {
        $return->raw = $file_get_contents;
    }
    if (false !== strpos($transfer['content_type'], 'application/json')) {
        $return->json = json_decode($return->raw);
        if (is_resource($fp)) {
            $meta_data = stream_get_meta_data($fp);
            unlink($meta_data['uri']);
        }
    }
    $code = $transfer['http_code'];
    if (200 != $code && !isset($return->json)) {
        $return->json = new stdClass();
        $return->json->error = new stdClass();
        $return->json->error->message = 'Error performing HTTP request';
        $return->json->error->code = $code;
    }
    $return->transfer = $transfer;

    return $return;
}

function getFormatBytes($bytes, int $round = 1): string
{
    if (!is_numeric($bytes)) {
        return (string) $bytes;
    }
    if ($bytes < 1000) {
        return "$bytes B";
    }
    $units = ['KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    foreach ($units as $k => $v) {
        $multiplier = pow(1000, $k + 1);
        $threshold = $multiplier * 1000;
        if ($bytes < $threshold) {
            $size = round($bytes / $multiplier, $round);

            return "$size $v";
        }
    }
}

function getBytesToMb($bytes, int $round = 2): float
{
    $mb = $bytes / pow(10, 6);
    if ($round) {
        $mb = round($mb, $round);
    }

    return $mb;
}

function downloadFile(string $url, array $params, string $filePath, bool $post = true): object
{
    $fp = fopen($filePath, 'wb+');
    if (!$fp) {
        throw new Exception("Can't open temp file " . $filePath . ' (wb+)');
    }
    $ops = [
        CURLOPT_FILE => $fp,
    ];
    if ($params !== []) {
        $ops[CURLOPT_POSTFIELDS] = http_build_query($params);
    }
    if ($post) {
        $ops[CURLOPT_POST] = true;
    }
    $curl = curl($url, $ops);
    fclose($fp);

    return $curl;
}

function downloadAction(string $workingDir, array $params): Response
{
    $fileBasename = 'chevereto.zip';
    $filePath = $workingDir . '/' . $fileBasename;
    unlinkIfExists($filePath);
    $isPost = false;
    $zipBall = ZIP_BALL;
    $tag = $params['tag'] ?? 'latest';
    $zipBall = str_replace('%tag%', $tag, $zipBall);
    $isPost = true;
    $curl = downloadFile($zipBall, $params, $filePath, $isPost);
    if (isset($curl->json->error)) {
        throw new RuntimeException(
            $curl->json->error->message
            . sprintf(' [%s]', $curl->json->error->code),
            $curl->json->status_code
        );
    }
    if ($curl->transfer['http_code'] !== 200) {
        $error = '[HTTP ' . $curl->transfer['http_code'] . '] ' . $zipBall;

        throw new RuntimeException($error, $curl->transfer['http_code']);
    }
    $fileSize = filesize($filePath);

    return new Response(
        strtr('Downloaded %f (%w @%s)', [
            '%f' => $fileBasename,
            '%w' => getFormatBytes($fileSize),
            '%s' => getBytesToMb($curl->transfer['speed_download']) . 'MB/s.',
        ]),
        [
            'fileBasename' => $fileBasename,
            'filePath' => $filePath,
        ]
    );
}

function extractAction(string $pathTo, string $filePath): Response
{
    if (!file_exists($pathTo) && !mkdir($pathTo)) {
        throw new Exception(sprintf("Working path %s doesn't exists and can't be created", $pathTo), 500);
    }
    if (!is_readable($pathTo)) {
        throw new Exception(sprintf('Working path %s is not readable', $pathTo), 500);
    }
    if (!is_readable($filePath)) {
        throw new Exception(sprintf("Can't read %s", basename($filePath)), 500);
    }
    $zip = new ZipArchive();
    $timeStart = microtime(true);
    $zipOpen = $zip->open($filePath);
    if ($zipOpen !== true) {
        throw new Exception(strtr("Can't extract %f - %m (ZipArchive #%z)", [
            '%f' => $filePath,
            '%m' => 'ZipArchive ' . $zipOpen . ' error',
            '%z' => $zipOpen,
        ]), 500);
    }
    $numFiles = $zip->numFiles - 1;
    $extraction = $zip->extractTo($pathTo);
    if (!$extraction) {
        throw new Exception("Unable to extract to");
    }
    $zip->close();
    $timeTaken = round(microtime(true) - $timeStart, 2); //
    clearstatcache(true, $pathTo);

    return new Response(
        strtr('Extraction completed for %n files in %ss', ['%n' => $numFiles, '%s' => $timeTaken]),
        [
            'numFiles' => $numFiles,
            'timeTaken' => $timeTaken,
        ]
    );
}

function abort(string $message)
{
    logger('[ERROR] ' . $message);
    die(255);
}

function passthruEnabled(): bool
{
    if (!function_exists('passthru')) {
        return false;
    }
    $disabled = explode(',', ini_get('disable_functions'));

    return !in_array('passthru', $disabled);
}

function unlinkIfExists(string $file): void
{
    if (!file_exists($file)) {
        return;
    }
    unlink($file);
}

class Response
{
    public string $message;

    public array $data;

    public function __construct(string $message, array $data = [])
    {
        $this->message = $message;
        $this->data = $data;
    }
}
