<?php

namespace Arara\Process\Handler;

/**
 * @covers Arara\Process\Handler\SignalTerminate
 */
class SignalTerminateTest extends \TestCase
{
    public function testShouldExitAs0()
    {
        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('quit'))
            ->getMock();

        $control
            ->expects($this->once())
            ->method('quit')
            ->with(0);

        $handler = new SignalTerminate($control);
        $handler(SIGTERM);
    }
}
