--TEST--
Should be able to handle timeout event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-timeout.php'; ?>
--EXPECTF--
This child process will sleep for 5 seconds
{
    "isRunning": true,
    "processId": %d,
    "timeout": 1,
    "startTime": %d,
    "exitCode": 3,
    "finishTime": %d
}
