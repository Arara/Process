<?php

/* Bootstrap */
require_once __DIR__ . '/../bootstrap.php';

use Arara\Process\Signal;

$signal = new Signal();
$signal->alarm(5);

$i = 0;
while(++$i) {
    echo $i . PHP_EOL;
    sleep(1);
}
