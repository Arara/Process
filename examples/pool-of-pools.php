<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/action-class.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;

$control = new Control();
$pool1 = new Pool(1);
$pool1->attach(new Child(new \ActionClass('1-1'), $control));
$pool1->attach(new Child(new \ActionClass('1-2'), $control));
$pool1->attach(new Child(new \ActionClass('1-3'), $control));
$pool1->attach(new Child(new \ActionClass('1-4'), $control));
$pool1->attach(new Child(new \ActionClass('1-5'), $control));

$pool2 = new Pool(3);
$pool2->attach(new Child(new \ActionClass('2-1'), $control));
$pool2->attach(new Child(new \ActionClass('2-2'), $control));
$pool2->attach(new Child(new \ActionClass('2-3'), $control));
$pool2->attach(new Child(new \ActionClass('2-4'), $control));
$pool2->attach(new Child(new \ActionClass('2-5'), $control));
$pool2->attach(new Child(new \ActionClass('2-6'), $control));

// Pools does not run into a different process
$pool = new Pool(5);
$pool->attach($pool1);
$pool->attach($pool2);
$pool->start();
$pool->wait();
