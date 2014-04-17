<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/action-class.php';

use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Pool;

$control = new Control();
$pool = new Pool(3);
$pool->attach(new Child(new \ActionClass(1), $control));
$pool->attach(new Child(new \ActionClass(2), $control));
$pool->attach(new Child(new \ActionClass(3), $control));
$pool->attach(new Child(new \ActionClass(4), $control));
$pool->attach(new Child(new \ActionClass(5), $control));
$pool->attach(new Child(new \ActionClass(6), $control));
$pool->attach(new Child(new \ActionClass(7), $control));
$pool->attach(new Child(new \ActionClass(8), $control));
$pool->attach(new Child(new \ActionClass(9), $control));
$pool->start();
$pool->wait();
