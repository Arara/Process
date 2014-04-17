<?php

namespace Arara\Process\Control\Signal;

/**
 * @covers Arara\Process\Control\Signal\TerminateHandler
 */
class TerminateHandlerTest extends \TestCase
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

        $handler = new TerminateHandler($control);
        $handler(SIGTERM);
    }
}
