<?php

namespace Arara\Process\Control;

/**
 * @covers Arara\Process\Control\Status
 */
class StatusTest extends \TestCase
{
    public function testShouldAcceptAnWaitStatusOnConstructor()
    {
        $waitStatus = 0;

        $status = new Status($waitStatus);

        $this->assertAttributeSame($waitStatus, 'status', $status);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Invalid wait status given
     */
    public function testShouldThrowsAnExceptionWhenStatusIsNotValid()
    {
        $status = new Status('0');
    }

    public function testShouldReturnExitStatus()
    {
        $GLOBALS['arara']['pcntl_wexitstatus']['return'] = 0;
        $status = new Status(0);

        $this->assertEquals(0, $status->getExitStatus());
    }

    public function testShouldReturnStopSignal()
    {
        $GLOBALS['arara']['pcntl_wstopsig']['return'] = SIGSTOP;
        $status = new Status(0);

        $this->assertEquals(SIGSTOP, $status->getStopSignal());
    }

    public function testShouldReturnTerminateSignal()
    {
        $GLOBALS['arara']['pcntl_wtermsig']['return'] = SIGTERM;
        $status = new Status(0);

        $this->assertEquals(SIGTERM, $status->getTerminateSignal());
    }

    public function testShouldReturnIfIsExited()
    {
        $GLOBALS['arara']['pcntl_wifexited']['return'] = true;
        $status = new Status(0);

        $this->assertTrue($status->isExited());
    }

    public function testShouldReturnIfIsSignaled()
    {
        $GLOBALS['arara']['pcntl_wifsignaled']['return'] = true;
        $status = new Status(0);

        $this->assertTrue($status->isSignaled());
    }

    public function testShouldReturnIfIsStopped()
    {
        $GLOBALS['arara']['pcntl_wifstopped']['return'] = true;
        $status = new Status(0);

        $this->assertTrue($status->isStopped());
    }

    public function testShouldReturnAsSuccessfulWhenExitStatusIsZero()
    {
        $GLOBALS['arara']['pcntl_wexitstatus']['return'] = 0;
        $status = new Status(0);

        $this->assertTrue($status->isSuccessful());
    }

    public function testShouldReturnAsUnsuccessfulWhenExitStatusIsNotZero()
    {
        $GLOBALS['arara']['pcntl_wexitstatus']['return'] = 3;
        $status = new Status(0);

        $this->assertFalse($status->isSuccessful());
    }
}
