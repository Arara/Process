<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Control;

$child = new Child(
    new Callback(
        function (Control $control) {
            echo 'This child process will sleep for 0.5 seconds'.PHP_EOL;
            $control->flush(0.5);
            echo 'This child just woke up'.PHP_EOL;
        }
    ),
    new Control()
);
$child->start();

$child->wait();
if (! $child->isRunning()) {
    echo 'Child is not running anymore'.PHP_EOL;
}
