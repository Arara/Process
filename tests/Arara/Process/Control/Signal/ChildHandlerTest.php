<?php

namespace Arara\Process\Control\Signal;

/**
 * @covers Arara\Process\Control\Signal\ChildHandler
 */
class ChildHandlerTest extends \TestCase
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

        $handler = new ChildHandler($control);
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

        $handler = new ChildHandler($control);
        $handler(SIGCHLD);
    }
}
