<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Arara\Process\Action\Action;
use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$action = new Callback(function (Control $control) {
    echo 'This is the process action of PID ' . $control->info()->getId() . PHP_EOL;
});
$action->bind(Action::EVENT_START, function (Control $control, Context $context) {
    echo 'Start: ' . json_encode($context->toArray(), (defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : 0)) . PHP_EOL;
});

$control = new Control();
$child = new Child($action, $control);
$child->start();
$child->wait();
