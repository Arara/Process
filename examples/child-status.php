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
            echo 'Child process is '.$control->info()->getId().PHP_EOL;
            $control->flush(1);
        }
    ),
    $control
);
echo 'Parent process is '.$control->info()->getId().PHP_EOL;

$child->start();

if ($child->getStatus()->isSuccessful()) {
    echo 'Child successfully finished'.PHP_EOL;
}
