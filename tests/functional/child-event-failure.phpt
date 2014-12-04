--TEST--
Should be able to handle failure event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-failure.php'; ?>
--EXPECTF--
{
    "isRunning": true,
    "processId": %d,
    "timeout": 0,
    "startTime": %d,
    "exception": {
        "class": "DomainException",
        "message": "An exception was thrown",
        "code": 13,
        "file": "%s/examples/child-event-failure.php",
        "line": %d
    },
    "exitCode": 1,
    "finishTime": %d
}
