<?php

namespace Arara\Process;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Context
 */
class ContextTest extends TestCase
{
    public function testShouldDefineAndReturnAPropertyValue()
    {
        $value = 9172971;

        $context = new Context();
        $context->foo = 9172971;

        $this->assertSame($value, $context->foo);
    }

    public function testShouldReturnNullWhenPropertyDoesNotExists()
    {
        $context = new Context();

        $this->assertNull($context->foo);
    }

    public function testShouldReturnPropertiesAsArray()
    {
        $context = new Context();
        $context->foo = true;
        $context->bar = false;

        $expectedValue = array('foo' => true, 'bar' => false);
        $actualValue = $context->toArray();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testShouldAcceptAnArrayOfPropertiesOnConstructor()
    {
        $data = array(
            'foo' => true,
            'bar' => false,
        );
        $context = new Context($data);

        $expectedValue = array('foo' => true, 'bar' => false);
        $actualValue = $context->toArray();

        $this->assertSame($expectedValue, $actualValue);
    }

    public function testShouldDumpExceptionWhenConveringToArray()
    {
        $exception = new \DomainException('My message', 42);

        $context = new Context();
        $context->exception = $exception;

        $actualValue = $context->toArray();
        $expectedValue = array(
            'exception' => array(
                'class'     => get_class($exception),
                'message'   => $exception->getMessage(),
                'code'      => $exception->getCode(),
                'file'      => $exception->getFile(),
                'line'      => $exception->getLine(),
            ),
        );

        $this->assertSame($actualValue, $expectedValue);
    }
}
