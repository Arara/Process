--TEST--
Should be able to execute a shell command.
--FILE--
<?php require __DIR__ . '/../../examples/command.php'; ?>
--EXPECTF--
{
    "isRunning": true,
    "processId": %d,
    "timeout": 0,
    "startTime": %d,
    "command": "/usr/bin/env find '%s/examples' '-name' '*' '-type' 'f'",
    "outputTail": "%s/examples/%s.php",
    "outputString": "%s",
    "outputLines": [
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php",
        "%s/examples/%s.php"
    ],
    "returnValue": 0,
    "exitCode": 0,
    "finishTime": %d
}
