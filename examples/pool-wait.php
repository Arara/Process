<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;
use Arara\Test\TestAction;

$control = new Control();
$pool = new Pool(6);
$pool->start();
for ($index = 1; $index <= 3; $index++) {
    if (! $pool->isRunning()) {
        continue;
    }

    $action = new TestAction($index);
    $child = new Child($action, $control);
    $pool->attach($child);
}
$pool->wait();

echo 'Pool is not running anymore'.PHP_EOL;
