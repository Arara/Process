--TEST--
Should be able to handle success event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-success.php'; ?>
--EXPECTF--
This is the process action of PID %d
{
    "isRunning": true,
    "processId": %d,
    "timeout": 0,
    "startTime": %d,
    "exitCode": 0,
    "finishTime": %d
}
