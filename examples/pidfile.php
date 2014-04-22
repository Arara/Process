<?php

declare(ticks=1);

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/action-class.php';

use Arara\Process\Control;
use Arara\Process\Pidfile;

try {
    $control = new Control();
    $pidfile = new Pidfile($control);
    $pidfile->initialize();
    echo 'Will sleep for 10 seconds, try to run it in another terminal' . PHP_EOL;
    sleep(10);
    $pidfile->finalize();
} catch (Exception $exception) {
    echo $exception->getMessage() . PHP_EOL;
}

echo 'Finished' . PHP_EOL;
