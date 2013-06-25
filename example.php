<?php

/* Bootstrap */
require_once __DIR__ . '/bootstrap.php';

use Arara\Process\Manager;
use Arara\Process\Item;
use Arara\Process\Ipc\SharedMemory as Ipc;

$manager = new Manager(15);

// Linux users
exec("awk -F ':' '{ print $1,$3,$4 }' /etc/passwd", $users);
$users = array_filter($users);
echo 'Running ' . count($users) . ' process, 15 simultaneously' . PHP_EOL;
foreach($users as $key => $user) {
    if (0 === strpos($user, '#')) {
        continue;
    }
    list($username, $uid, $gid) = explode(' ', $user);
    $process = new Item(
        function () use ($key, $username) {
            $number = sprintf('%02d', $key + 1);
            $content = "Job {$key} for {$username}";
            $filename = sys_get_temp_dir() . "/fork-{$username}-{$key}";
            echo $content . PHP_EOL;
            echo $filename . PHP_EOL;
            file_put_contents($filename, $content);
            sleep(3);
        },
        new Ipc(),
        $uid,
        $gid
    );
    $process->setCallback(
        function (Ipc $ipc) {
            echo $ipc->load('output') . PHP_EOL;
        },
        Item::STATUS_SUCESS | Item::STATUS_ERROR | Item::STATUS_FAIL
    );

    try {

        $manager->addChild($process);

    } catch (Exception $exception) {

        echo $exception->getMessage(), PHP_EOL,
             str_repeat('-', strlen($exception->getMessage())), PHP_EOL,
             $exception->getTraceAsString(), PHP_EOL;

    }
}

$queued = date('Y-m-d H:i:s');
$manager->wait();

echo 'Queued all process at ' . $queued . ' and finished at ' . date('Y-m-d H:i:s') . PHP_EOL; 
