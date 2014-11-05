<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/functions-arara-process.php';
require_once __DIR__ . '/functions-arara-process-control.php';

class TestCase extends PHPUnit_Framework_TestCase
{
    final protected function setUp()
    {
        $GLOBALS['arara'] = array();
        $this->init();
    }

    protected function init()
    {
        // Some body, if needed
    }

    final protected function tearDown()
    {
        $GLOBALS['arara'] = array();
        $this->finish();
    }

    protected function finish()
    {
        // Some body, if needed
    }

    protected function setObjectPropertyValue($object, $property, $value)
    {
        $reflection = new ReflectionProperty($object, $property);
        $reflection->setAccessible(true);
        $reflection->setValue($object, $value);
    }

    protected function getObjectPropertyValue($object, $property)
    {
        $reflection = new ReflectionProperty($object, $property);
        $reflection->setAccessible(true);

        return $reflection->getValue($object);
    }
}
