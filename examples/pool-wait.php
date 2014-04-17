<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/action-class.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;

$control = new Control();
$pool = new Pool(6);
$pool->start();
for ($count=1; $count <= 9 ; $count++) {
    if (! $pool->isRunning()) {
        continue;
    }

    $pool->attach(new Child(new \ActionClass($count), $control));
    if ($count % 3 == 0) {
        $pool->wait();
    }
}
$pool->wait();
