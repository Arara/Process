<?php

namespace Arara\Test;

use PHPUnit_Framework_Exception;
use PHPUnit_Framework_TestCase;
use ReflectionProperty;

class TestCase extends PHPUnit_Framework_TestCase
{
    /**
     * Array of overwritten functions.
     *
     * @var array
     */
    protected $overwrites = array();

    /**
     * {@inheritDoc}
     */
    final protected function setUp()
    {
        $this->overwrites = array();

        $this->init();
    }

    protected function init()
    {
        // Some body, if needed
    }

    /**
     * {@inheritDoc}
     */
    final protected function tearDown()
    {
        foreach ($this->overwrites as $function) {
            $this->restore($function);
        }

        $this->finish();
    }

    protected function finish()
    {
        // Some body, if needed
    }

    /**
     * Overwrites a native PHP function.
     *
     * @param  string $functionName
     * @param  callable $callback
     * @return self
     */
    protected function overwrite($functionName, $callback)
    {
        if (in_array($functionName, $this->overwrites)) {
            throw new PHPUnit_Framework_Exception(sprintf('"%s" is already overwritten', $functionName));
        }

        uopz_backup($functionName);
        uopz_function($functionName, $callback);

        $this->overwrites[] = $functionName;

        return $this;
    }

    /**
     * Restores an overwritten function.
     *
     * @param  string $functionName
     * @return self
     */
    protected function restore($functionName)
    {
        $key = array_search($functionName, $this->overwrites);
        if (false === $key) {
            throw new PHPUnit_Framework_Exception(sprintf('"%s" is not overwritten', $functionName));
        }
        uopz_restore($functionName);
        unset($this->overwrites[$key]);

        return $this;
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
