--TEST--
Should be able to terminate child.
--FILE--
<?php require __DIR__ . '/../../examples/child-terminate.php'; ?>
--EXPECTF--
This child process will sleep for 5 seconds
Child is running
Child was terminated
