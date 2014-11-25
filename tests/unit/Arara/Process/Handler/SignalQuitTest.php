<?php

namespace Arara\Process\Handler;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Handler\SignalQuit
 */
class SignalQuitTest extends TestCase
{
    public function testShouldExitAs4()
    {
        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('quit'))
            ->getMock();

        $control
            ->expects($this->once())
            ->method('quit')
            ->with(4);

        $handler = new SignalQuit($control);
        $handler(SIGQUIT);
    }
}
