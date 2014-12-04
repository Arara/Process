--TEST--
Should be able to wait on child.
--FILE--
<?php require __DIR__ . '/../../examples/child-wait.php'; ?>
--EXPECTF--
This child process will sleep for 0.5 seconds
This child just woke up
Child is not running anymore
