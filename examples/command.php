<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Arara\Process\Action\Command;
use Arara\Process\Child;
use Arara\Process\Context;
use Arara\Process\Control;

try {
    $command = new Command('find', array(__DIR__, '-name' => '*', '-type' => 'f'));
    $command->bind(Command::EVENT_FINISH, function (Control $control, Context $context) {
        echo json_encode($context->toArray(), (JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT)) . PHP_EOL;
    });

    $child = new Child($command, new Control());
    $child->start();
    $child->wait();

} catch (Exception $e) {
    echo $e->getMessage() . PHP_EOL;
    echo $e->getTraceAsString() . PHP_EOL;
}
