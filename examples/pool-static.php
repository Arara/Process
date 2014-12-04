<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;
use Arara\Test\TestAction;

$control = new Control();
$pool = new Pool(3);
$pool->attach(new Child(new TestAction(1), $control));
$pool->attach(new Child(new TestAction(2), $control));
$pool->attach(new Child(new TestAction(3), $control));
$pool->attach(new Child(new TestAction(4), $control));
$pool->attach(new Child(new TestAction(5), $control));
$pool->attach(new Child(new TestAction(6), $control));
$pool->attach(new Child(new TestAction(7), $control));
$pool->attach(new Child(new TestAction(8), $control));
$pool->attach(new Child(new TestAction(9), $control));
$pool->start();
$pool->wait();
