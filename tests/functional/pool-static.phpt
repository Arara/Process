--TEST--
Should be able to add children before start the Pool.
--FILE--
<?php require __DIR__ . '/../../examples/pool-static.php'; ?>
--EXPECTF--
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
