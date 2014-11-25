<?php

namespace Arara\Process\Handler;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Handler\SignalChild
 */
class SignalChildTest extends TestCase
{
    public function testShouldWaitChildrenFinish()
    {
        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('wait'))
            ->getMock();

        $control
            ->expects($this->once())
            ->method('wait')
            ->with(0, (WNOHANG | WUNTRACED))
            ->will($this->returnValue(0));

        $handler = new SignalChild($control);
        $handler(SIGCHLD);
    }

    public function testShouldTryToWaitUntilChildrenFinish()
    {
        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('wait'))
            ->getMock();

        $control
            ->expects($this->exactly(2))
            ->method('wait')
            ->with(0, (WNOHANG | WUNTRACED))
            ->will($this->onConsecutiveCalls(999, 0));

        $handler = new SignalChild($control);
        $handler(SIGCHLD);
    }
}
