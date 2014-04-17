<?php

namespace Arara\Process\Control\Signal;

/**
 * @covers Arara\Process\Control\Signal\QuitHandler
 */
class QuitHandlerTest extends \TestCase
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

        $handler = new QuitHandler($control);
        $handler(SIGQUIT);
    }
}
