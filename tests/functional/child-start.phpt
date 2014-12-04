--TEST--
Should be able to start child.
--FILE--
<?php require __DIR__ . '/../../examples/child-start.php'; ?>
--EXPECTF--
Parent process is %d
Child process is %d
