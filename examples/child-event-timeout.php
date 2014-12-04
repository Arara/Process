<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Action;
use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$action = new Callback(function (Control $control) {
    echo 'This child process will sleep for 5 seconds'.PHP_EOL;
    $control->flush(5);
    echo 'This child just woke up'.PHP_EOL;
});
$action->bind(Action::EVENT_TIMEOUT, function (Context $context) {
    echo json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).PHP_EOL;
});

$control = new Control();
$child = new Child($action, $control, 1);
$child->start();
$child->wait();
