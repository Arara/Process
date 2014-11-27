<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Arara\Process\Action\Action;
use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$action = new Callback(function (Control $control) {
    echo 'Child process will sleep for 10 seconds' . PHP_EOL;
    sleep(10);
    echo 'Child just awakened' . PHP_EOL;
});
$action->bind(Action::EVENT_TIMEOUT, function (Control $control, Context $context) {
    echo 'Timeout: ' . json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) . PHP_EOL;
});

$control = new Control();
$child = new Child($action, $control, 2);
$child->start();
$child->wait();
