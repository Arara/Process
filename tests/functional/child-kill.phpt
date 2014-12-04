--TEST--
Should be able to kill child.
--FILE--
<?php require __DIR__ . '/../../examples/child-kill.php'; ?>
--EXPECTF--
This child process will sleep for 5 seconds
Child is running
Child was killed
