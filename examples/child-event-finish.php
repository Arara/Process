<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Action;
use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$action = new Callback(function (Control $control) {
    echo 'This is the process action of PID '.$control->info()->getId().PHP_EOL;
});
$action->bind(Action::EVENT_FINISH, function (Context $context) {
    echo json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).PHP_EOL;
});

$control = new Control();
$child = new Child($action, $control);
$child->start();
$child->wait();
