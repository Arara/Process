<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Control;

$action = new Callback(function (Control $control) {
    echo 'Child process will sleep for 5 seconds' . PHP_EOL;
    sleep(5);
    echo 'Child just awakened' . PHP_EOL;
});
$control = new Control();
$child = new Child($action, $control);
echo 'Parent process is ' . $control->info()->getId() . PHP_EOL;

$child->start();
if ($child->isRunning()) {
    echo 'Child is running' . PHP_EOL;
}
$child->kill();
if (! $child->isRunning()) {
    echo 'Child was killed' . PHP_EOL;
}
