<?php

/* Bootstrap */
require_once __DIR__ . '/bootstrap.php';

use Arara\Process\Ipc\File as Ipc;
use Arara\Process\Process;
use Arara\Process\Manager;

$manager = new Manager(15);

// Linux users
exec("awk -F ':' '{ print $1,$3,$4 }' /etc/passwd", $users);
$users = array_filter($users);
echo 'Running ' . count($users) . ' process, 15 simultaneously' . PHP_EOL;
foreach($users as $key => $user) {
    $job = sprintf('%03d', ($key + 1));
    if (0 === strpos($user, '#')) {
        continue;
    }
    list($username, $uid, $gid) = explode(' ', $user);
    $process = new Process(
        function (Ipc $ipc) use ($job, $username) {
            $ipc->save('startedAt', date('Y-m-d H:i:s'));
            echo "Running job for {$username}";
            sleep(3);
            $ipc->save('finishedAt', date('Y-m-d H:i:s'));
        },
        new Ipc(),
        $uid,
        $gid
    );
    $process->setCallback(
        function (Ipc $ipc) use ($job) {
            echo "[{$job}] Finished at: {$ipc->load('finishedAt')}" . PHP_EOL;
            echo "[{$job}] Output:      {$ipc->load('output')}" . PHP_EOL;
        },
        Process::STATUS_SUCESS | Process::STATUS_ERROR | Process::STATUS_FAIL
    );

    try {
        $manager->addChild($process);
        usleep(100000);
        echo "[{$job}] Started at   {$process->getIpc()->load('startedAt')}" . PHP_EOL;

    } catch (Exception $exception) {
        echo $exception->getMessage(), PHP_EOL;
        echo str_repeat('-', strlen($exception->getMessage())), PHP_EOL;
        echo $exception->getTraceAsString(), PHP_EOL;
    }
}

$queued = date('Y-m-d H:i:s');
$manager->wait();
$finished = date('Y-m-d H:i:s');

echo 'Queued all process at ' . $queued . ' and finished at ' . $finished . PHP_EOL;
