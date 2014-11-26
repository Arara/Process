<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;
use Arara\Test\TestAction;

$control = new Control();
$pool1 = new Pool(1);
$pool1->attach(new Child(new TestAction('11'), $control));
$pool1->attach(new Child(new TestAction('12'), $control));
$pool1->attach(new Child(new TestAction('13'), $control));
$pool1->attach(new Child(new TestAction('14'), $control));
$pool1->attach(new Child(new TestAction('15'), $control));

$pool2 = new Pool(3);
$pool2->attach(new Child(new TestAction('21'), $control));
$pool2->attach(new Child(new TestAction('22'), $control));
$pool2->attach(new Child(new TestAction('23'), $control));
$pool2->attach(new Child(new TestAction('24'), $control));
$pool2->attach(new Child(new TestAction('25'), $control));
$pool2->attach(new Child(new TestAction('26'), $control));

// Pools does not run into a different process
$pool = new Pool(5);
$pool->attach($pool1);
$pool->attach($pool2);
$pool->start();
$pool->wait();
