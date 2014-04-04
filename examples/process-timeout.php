<?php

declare(ticks = 1); // Dont' forget it.

/* Bootstrap */
require_once __DIR__ . '/../bootstrap.php';

use Arara\Process\Ipc\File as Ipc;
use Arara\Process\Process;
use Arara\Process\Signal;

$ipc = new Ipc();
$process = new Process(
    function () {
        echo 'Sleeping for 10 seconds...' . PHP_EOL;
        sleep(10);
        echo 'Awake!' . PHP_EOL;
    },
    $ipc
);
$process->setTimeout(5);
$process->setCallback(
    function () {
        echo 'I got a timeout after 5 seconds' . PHP_EOL;
    },
    Process::STATUS_TIMEOUT
);
$process->start(new Signal());
$process->wait();
echo $ipc->load('output');
