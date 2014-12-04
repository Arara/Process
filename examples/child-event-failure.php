<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Action;
use Arara\Process\Action\Callback;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$action = new Callback(function () {
    throw new DomainException('An exception was thrown', 13);
});
$action->bind(Action::EVENT_FAILURE, function (Context $context) {
    echo json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).PHP_EOL;
});

$control = new Control();
$child = new Child($action, $control);
$child->start();
$child->wait();
