<?php

declare (ticks = 1);

require_once __DIR__.'/../vendor/autoload.php';

use Arara\Process\Action\Daemon;
use Arara\Process\Child;
use Arara\Process\Control;
use Arara\Process\Context;

$daemon = new Daemon(
    function (Daemon $daemon, Control $control) {
        openlog($daemon->getOption('name'), LOG_PID | LOG_PERROR, LOG_LOCAL0);

        $control->flush(60);
    }
);
$daemon->bind(Daemon::EVENT_SUCCESS, function () {
    syslog(LOG_INFO, 'Daemon was successfully finished');
});
$daemon->bind(Daemon::EVENT_ERROR | Daemon::EVENT_FAILURE, function (Context $context) {
    syslog(LOG_ERR, $context->exception->getMessage());
});
$daemon->bind(Daemon::EVENT_FINISH, function () {
    closelog();
});
$daemon->setOption('lock_dir', __DIR__);

$control = new Control();
$process = new Child($daemon, $control);

try {
    $args = $_SERVER['argv'];
    $script = array_shift($args);
    $usageMessage = sprintf('php %s {start|stop|status}', $script);

    if (empty($args)) {
        throw new DomainException($usageMessage);
    }

    $action = array_shift($args);
    switch ($action) {
        case 'start':
            $process->start();
            fwrite(STDOUT, 'Started'.PHP_EOL);
            break;

        case 'stop':
            $process->terminate();
            fwrite(STDOUT, 'Stopped'.PHP_EOL);
            break;

        case 'status':
            fwrite(STDOUT, 'Daemon is '.($process->isRunning() ? '' : 'not ').'running'.PHP_EOL);
            break;

        default:
            throw new DomainException($usageMessage);
    }
} catch (DomainException $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    exit(1);
} catch (Exception $exception) {
    fwrite(STDERR, $exception->getMessage().PHP_EOL);
    fwrite(STDERR, $exception->getTraceAsString().PHP_EOL);
    exit(2);
}
