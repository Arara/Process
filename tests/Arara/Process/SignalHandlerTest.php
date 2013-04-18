<?php

namespace Arara\Process;

function pcntl_signal($signumber, $callback)
{
    $GLOBALS['pcntl_signal'][$signumber] = $callback;
}

function pcntl_wait(&$status, $options = 0)
{
    if (true === $GLOBALS['pcntl_wait']) {
        $GLOBALS['pcntl_wait'] = false;
    } else {
        $GLOBALS['pcntl_wait'] = true;
    }

    return !$GLOBALS['pcntl_wait'];
}
function usleep($time)
{
    $GLOBALS['usleep'] = $time;
}

class SignalHandlerTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $GLOBALS['pcntl_signal'] = array();
        $GLOBALS['pcntl_wait'] = false;
        $GLOBALS['usleep'] = null;
    }

    /**
     * @covers Arara\Process\SignalHandler::__construct
     */
    public function testShouldDefinedSignalsOnConstructor()
    {
        $handler = new SignalHandler();
        $expected = array(
            SIGINT => array($handler, 'handle'),
            SIGQUIT => array($handler, 'handle'),
            SIGTERM => array($handler, 'handle'),
            SIGCHLD => array($handler, 'handle'),
        );

        $this->assertSame($expected, $GLOBALS['pcntl_signal']);
    }

    /**
     * @covers Arara\Process\SignalHandler::handle
     */
    public function testShouldHandleInt()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(1);
        $handler->handle(SIGINT);
    }

    /**
     * @covers Arara\Process\SignalHandler::handle
     */
    public function testShouldHandleQuit()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(1);
        $handler->handle(SIGQUIT);
    }

    /**
     * @covers Arara\Process\SignalHandler::handle
     */
    public function testShouldHandleTerm()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\SignalHandler')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(0);
        $handler->handle(SIGTERM);
    }

    /**
     * @covers Arara\Process\SignalHandler::handle
     */
    public function testShouldHandleAChildThatAlreadyDies()
    {
        $handler = new SignalHandler();
        $handler->handle(SIGCHLD);

        $this->assertNull($GLOBALS['usleep']);
    }
    /**
     * @covers Arara\Process\SignalHandler::handle
     */
    public function testShouldHandleAChildThatIdDaying()
    {
        $GLOBALS['pcntl_wait'] = true;

        $handler = new SignalHandler();
        $handler->handle(SIGCHLD);

        $this->assertEquals($GLOBALS['usleep'], 1000);
    }

}
