<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;
use Arara\Test\TestAction;

$control = new Control();
$pool = new Pool(10);
$pool->start();
for ($index = 1; $index <= 9; $index++) {
    if (! $pool->isRunning()) {
        continue;
    }

    $action = new TestAction($index);
    $child = new Child($action, $control);
    $pool->attach($child);
    if (5 === $index) {
        $control->flush(0.5);
        $pool->kill();
    }
}
