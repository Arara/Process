--TEST--
Should be able to terminate an entire Pool.
--FILE--
<?php require __DIR__ . '/../../examples/pool-terminate.php'; ?>
--EXPECTF--
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
