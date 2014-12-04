<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Control;
use Arara\Process\Pidfile;

$control = new Control();
$pidfile = new Pidfile($control, 'myapp', __DIR__);

try {
    $pidfile->initialize();
    echo 'Will sleep for 10 seconds, try to run it in another terminal'.PHP_EOL;
    $control->flush(10);
    $pidfile->finalize(); // You may use register_shutdown_function([$pidfile, 'finalize']);
} catch (Exception $exception) {
    echo $exception->getMessage().PHP_EOL;
    echo "Running PID is #".$pidfile->getProcessId().PHP_EOL;
}

echo 'Finished'.PHP_EOL;
