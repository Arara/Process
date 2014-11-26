--TEST--
Should be able to add children to a running Pool.
--FILE--
<?php require __DIR__ . '/../../examples/pool-dynamic.php'; ?>
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
