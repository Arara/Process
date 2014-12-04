--TEST--
Should be able to create a Pool of Pools.
--FILE--
<?php require __DIR__ . '/../../examples/pool-of-pools.php'; ?>
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
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
