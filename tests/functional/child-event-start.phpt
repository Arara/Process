--TEST--
Should be able to handle start event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-start.php'; ?>
--EXPECTF--
{
    "isRunning": true,
    "processId": %d,
    "timeout": 0,
    "startTime": %d
}
This is the process action of PID %d
