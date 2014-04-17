<?php

namespace Arara\Process\Control\Signal;

/**
 * @covers Arara\Process\Control\Signal\InterruptHandler
 */
class InterruptHandlerTest extends \TestCase
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

        $handler = new InterruptHandler($control);
        $handler(SIGINT);
    }
}
