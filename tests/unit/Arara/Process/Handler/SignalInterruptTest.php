<?php

namespace Arara\Process\Handler;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Handler\SignalInterrupt
 */
class SignalInterruptTest extends TestCase
{
    public function testShouldExitAs3()
    {
        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('quit'))
            ->getMock();

        $control
            ->expects($this->once())
            ->method('quit')
            ->with(3);

        $handler = new SignalInterrupt($control);
        $handler(SIGINT);
    }
}
