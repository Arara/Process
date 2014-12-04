<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Control;

$control = new Control();
$child = new Child(
    new Callback(
        function (Control $control) {
            echo 'This child process will sleep for 5 seconds'.PHP_EOL;
            $control->flush(5);
            echo 'This child just woke up'.PHP_EOL;
        }
    ),
    $control
);

$child->start();
$control->flush(0.5);

if ($child->isRunning()) {
    echo 'Child is running'.PHP_EOL;
}

$child->terminate();
if (! $child->isRunning()) {
    echo 'Child was terminated'.PHP_EOL;
}
