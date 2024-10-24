<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevere\ThrowableHandler\Documents\PlainDocument;
use Chevereto\Config\Config;
use Chevereto\Legacy\Classes\DB;
use Chevereto\Legacy\Classes\Image;
use Chevereto\Legacy\Classes\L10n;
use Chevereto\Legacy\Classes\Lock;
use Chevereto\Legacy\Classes\Queue;
use Chevereto\Legacy\Classes\Settings;
use Chevereto\Legacy\Classes\User;
use Chevereto\Legacy\Classes\Variable;
use function Chevere\Message\message;
use function Chevere\ThrowableHandler\throwableHandler;
use function Chevere\Writer\writers;
use function Chevere\xrDebug\PHP\throwableHandler as XrDebugThrowableHandler;
use function Chevereto\Legacy\checkUpdates;
use function Chevereto\Legacy\cheveretoVersionInstalled;
use function Chevereto\Legacy\feedbackAlert;
use function Chevereto\Legacy\feedbackStep;
use function Chevereto\Legacy\G\datetime_add;
use function Chevereto\Legacy\G\datetime_sub;
use function Chevereto\Legacy\G\datetimegmt;
use function Chevereto\Legacy\getSetting;
use function Chevereto\Legacy\isSafeToExecute;
use function Chevereto\Legacy\updateCheveretoNews;
use function Chevereto\Vars\env;

if (getSetting('maintenance')) {
    echo "[!] Chevereto is in maintenance mode.\n";
    exit(255);
}
$jobs = [
    'deleteExpiredImages',
    'cleanUnconfirmedUsers',
    'removeDeleteLog',
    'storageDelete',
];
if ((bool) env()['CHEVERETO_ENABLE_UPDATE_CHECK']) {
    $jobs[] = 'checkForUpdates';
}
if ((bool) env()['CHEVERETO_ENABLE_NEWS_CHECK']) {
    $jobs[] = 'checkForNews';
}
if (Config::enabled()->htaccessCheck()) {
    $jobs[] = 'checkHtaccess';
}
shuffle($jobs);
$time_start = microtime(true);
$errors = [];
$namespace = env()['CHEVERETO_ID_HANDLE'] ?? false;
foreach ($jobs as $job) {
    if (! isSafeToExecute()) {
        echo "[OK] Exit - (time is up)\n";
        writeLastRan($time_start);
        exit(0);
    }
    feedbackStep('Job:', $job);

    try {
        $job();
    } catch (Throwable $throwable) {
        feedbackAlert($throwable->getMessage());
        $errors[$job] = $throwable->getMessage();
        $publicHandler = throwableHandler($throwable);
        if ($namespace) {
            $publicHandler = $publicHandler
                ->withId($namespace . $publicHandler->id());
        }
        $internalHandler = $publicHandler->withIsDebug(true);
        $docInternal = new PlainDocument($internalHandler);
        $logMessage = '[' . $publicHandler->id() . '] '
            . $docInternal->__toString()
            . "\n\n";

        try {
            $errorLog = ini_get('error_log');
            $fp = fopen($errorLog, 'a');
            fwrite($fp, $logMessage);
            fclose($fp);
        } catch (Throwable) {
            if (PHP_SAPI === 'cli') {
                error_log($logMessage);
            } else {
                writers()->error()->write($logMessage);
            }
        }
        $extra = <<<HTML
        <div class="throwable-message"><b>Incident {$publicHandler->id()}</b></div>
        <div class="throwable-message"><b>Backtrace</b></div>
        HTML;
        XrDebugThrowableHandler($throwable, $extra);
    }
}
writeLastRan($time_start);
function writeLastRan(float $time_start): void
{
    $datetimegmt = datetimegmt();
    Variable::set('cron_last_ran', $datetimegmt);
    $time_taken = microtime(true) - $time_start;
    $ceil = ceil($time_taken);
    $round = round($time_taken, 2);
    echo "--\n[DONE] Cron tasks ran @ {$datetimegmt}";
    echo "\n{$round}s\n--";
    if (version_compare(cheveretoVersionInstalled(), '4.2.0', '>=')) {
        $sql = <<<MySQL
        INSERT `%table_stats%` (stat_type, stat_date_gmt, stat_cron_time, stat_cron_runs)
            VALUES ("date", DATE("%date_gmt%"), %time%, 1)
            ON DUPLICATE KEY
                UPDATE stat_cron_time = stat_cron_time + %time%,
                stat_cron_runs = stat_cron_runs + 1;
        MySQL;
        $sql = (string) message(
            $sql,
            table_stats: DB::getTable('stats'),
            time: $ceil,
            date_gmt: $datetimegmt,
        );

        try {
            DB::queryExecute($sql);
        } catch (Throwable $e) {
            // Do nothing
        }
    }
    exit;
}
function echoLocked(string $job): void
{
    echo "* Job: {$job} [now locked]\n";
}
function storageDelete(): void
{
    $job = 'storage-delete';
    $lock = new Lock($job);
    if ($lock->create()) {
        Queue::process([
            'type' => $job,
        ]);
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function deleteExpiredImages(): void
{
    $job = 'delete-expired-images';
    $lock = new Lock($job);
    if ($lock->create()) {
        Image::deleteExpired(50);
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function cleanUnconfirmedUsers(): void
{
    $job = 'clean-unconfirmed-users';
    $lock = new Lock($job);
    if ($lock->create()) {
        User::cleanUnconfirmed(5);
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function removeDeleteLog(): void
{
    $job = 'remove-delete-log';
    $lock = new Lock($job);
    if ($lock->create()) {
        $db = DB::getInstance();
        $db->query('DELETE FROM ' . DB::getTable('deletions') . ' WHERE deleted_date_gmt <= :time;');
        $db->bind(':time', datetime_sub(datetimegmt(), 'P3M'));
        $db->exec();
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function checkForNews(): void
{
    if (! checkoutUpdate('news_check_datetimegmt', 'PT4H')) {
        feedbackAlert('Skipping news check');

        return;
    }
    L10n::setLocale(Settings::get('default_language'));
    $job = 'check-news';
    $lock = new Lock($job);
    if ($lock->create()) {
        updateCheveretoNews();
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function checkForUpdates(): void
{
    if (! checkoutUpdate('update_check_datetimegmt', 'P1D')) {
        feedbackAlert('Skipping updates check');

        return;
    }
    L10n::setLocale(Settings::get('default_language'));
    $job = 'check-updates';
    $lock = new Lock($job);
    if ($lock->create()) {
        checkUpdates();
        $lock->destroy();

        return;
    }
    echoLocked($job);
}
function checkoutUpdate(string $datetimeSetting, string $past): bool
{
    $get = Variable::fetch($datetimeSetting);

    return $get === null
        || datetime_add(
            $get,
            $past
        ) < datetimegmt();
}
function checkHtaccess()
{
    include __DIR__ . '/htaccess-enforce.php';
}
