--TEST--
Should be able to handle PHP error event.
--FILE--
<?php require __DIR__ . '/../../examples/child-event-error.php'; ?>
--EXPECTF--
{
    "isRunning": true,
    "processId": %d,
    "timeout": 0,
    "startTime": %d,
    "exception": {
        "class": "Arara\\Process\\Exception\\ErrorException",
        "message": "trim() expects parameter 1 to be string, array given",
        "code": 0,
        "file": "%s/examples/child-event-error.php",
        "line": %d
    },
    "exitCode": 2,
    "finishTime": %d
}
