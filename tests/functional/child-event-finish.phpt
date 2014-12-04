--TEST--
Should be able to handle finish event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-finish.php'; ?>
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
