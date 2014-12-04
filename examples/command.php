<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Command;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

$command = new Command('find', array(__DIR__, '-name' => '*', '-type' => 'f'));
$command->bind(Command::EVENT_FINISH, function (Context $context) {
    echo json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)).PHP_EOL;
});

$control = new Control();
$child = new Child($command, $control);
$child->start();
$child->wait();
