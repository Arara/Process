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

class SignalTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $GLOBALS['pcntl_signal'] = array();
        $GLOBALS['pcntl_wait'] = false;
        $GLOBALS['usleep'] = null;
    }

    /**
     * @covers Arara\Process\Signal::__construct
     */
    public function testShouldDefinedSignalsOnSetDefaultHandlers()
    {
        $handler = new Signal();
        $handler->setDefaultHandlers();
        $expected = array(
            SIGINT => array($handler, 'defaultHandler'),
            SIGQUIT => array($handler, 'defaultHandler'),
            SIGTERM => array($handler, 'defaultHandler'),
            SIGCHLD => array($handler, 'defaultHandler'),
        );

        $this->assertSame($expected, $GLOBALS['pcntl_signal']);
    }

    /**
     * @covers Arara\Process\Signal::handle
     */
    public function testShouldHandleInt()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\Signal')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(1);
        $handler->defaultHandler(SIGINT);
    }

    /**
     * @covers Arara\Process\Signal::handle
     */
    public function testShouldHandleQuit()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\Signal')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(1);
        $handler->defaultHandler(SIGQUIT);
    }

    /**
     * @covers Arara\Process\Signal::handle
     */
    public function testShouldHandleTerm()
    {
        $handler = $this
            ->getMockBuilder('Arara\Process\Signal')
            ->setMethods(array('quit'))
            ->getMock();
        $handler->expects($this->once())
                ->method('quit')
                ->with(0);
        $handler->defaultHandler(SIGTERM);
    }

    /**
     * @covers Arara\Process\Signal::handle
     */
    public function testShouldHandleAChildThatAlreadyDies()
    {
        $handler = new Signal();
        $handler->defaultHandler(SIGCHLD);

        $this->assertNull($GLOBALS['usleep']);
    }
    /**
     * @covers Arara\Process\Signal::handle
     */
    public function testShouldHandleAChildThatIdDaying()
    {
        $GLOBALS['pcntl_wait'] = true;

        $handler = new Signal();
        $handler->defaultHandler(SIGCHLD);

        $this->assertEquals($GLOBALS['usleep'], 1000);
    }

}
