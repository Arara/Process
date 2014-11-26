--TEST--
Should be able to wait an entire Pool.
--FILE--
<?php require __DIR__ . '/../../examples/pool-wait.php'; ?>
--EXPECTF--
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
[%s] PID: %d, Id: %d, Event: Execution
Pool is not running anymore
