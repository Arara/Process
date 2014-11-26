--TEST--
Should be able to get child's status.
--FILE--
<?php require __DIR__ . '/../../examples/child-status.php'; ?>
--EXPECTF--
Parent process is %d
Child process is %d
Child successfully finished
