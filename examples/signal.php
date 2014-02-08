<?php

/* Bootstrap */
require_once __DIR__ . '/../bootstrap.php';

use Arara\Process\Signal;

$i = 0;
while($i++ < 5) {
    $signal = new Signal();
    $signal->handle(SIGINT, function () use ($i) {
        echo "\r";
        echo "Aborted on {$i}!" . PHP_EOL;
        exit(1);
    });
    echo $i . PHP_EOL;
    sleep(1);
}
