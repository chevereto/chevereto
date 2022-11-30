<?php

/*
 * This file is part of Chevereto.
 *
 * (c) Rodolfo Berrios <rodolfo@chevereto.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Chevereto\Legacy\Classes\Import;
use function Chevereto\Legacy\feedbackAlert;
use function Chevereto\Legacy\isSafeToExecute;
use function Chevereto\Vars\env;

if (!(bool) env()['CHEVERETO_ENABLE_BULK_IMPORTER']) {
    feedbackAlert('Bulk importer is disabled');
    die(255);
}
$threadID = getenv('THREAD_ID') ?: 0;
$loop = 1;
do {
    Import::refresh();
    $jobs = Import::autoJobs();
    if ($jobs === []) {
        echo "~They took our jobs!~\n";
        echo "[OK] No jobs left.\n";
        die(0);
    }
    $id = $jobs[0]['import_id'];
    $import = new Import();
    $import->id = $id;
    $import->thread = (int) $threadID;
    $import->get();
    if ($import->isLocked()) {
        $import->edit(['status' => 'paused']);
        echo "> Job locked for id #$id\n";
    } else {
        echo "* Processing job id #$id\n";
        $import->process();
    }
    $loop++;
} while (isSafeToExecute());
echo "--\n[OK] Automatic importing looped $loop times ~ /dashboard/bulk for stats\n";
die(0);
