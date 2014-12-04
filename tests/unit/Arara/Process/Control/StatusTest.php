<?php

namespace Arara\Process\Control;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Control\Status
 */
class StatusTest extends TestCase
{
    public function testShouldAcceptAnWaitStatusOnConstructor()
    {
        $waitStatus = 0;

        $status = new Status($waitStatus);

        $this->assertAttributeSame($waitStatus, 'status', $status);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Invalid wait status given
     */
    public function testShouldThrowsAnExceptionWhenStatusIsNotValid()
    {
        $status = new Status('0');
    }

    public function testShouldReturnExitStatus()
    {
        $expectedExitStatus = 0;

        $this->overwrite(
            'pcntl_wexitstatus',
            function () use ($expectedExitStatus) {
                return $expectedExitStatus;
            }
        );

        $status = new Status(0);
        $actualExitStatus = $status->getExitStatus();

        $this->assertEquals($expectedExitStatus, $actualExitStatus);
    }

    public function testShouldReturnStopSignal()
    {
        $expectedSignal = SIGSTOP;

        $this->overwrite(
            'pcntl_wstopsig',
            function () use ($expectedSignal) {
                return $expectedSignal;
            }
        );

        $status = new Status(0);
        $actualSignal = $status->getStopSignal();

        $this->assertEquals($expectedSignal, $actualSignal);
    }

    public function testShouldReturnTerminateSignal()
    {
        $expectedSignal = SIGTERM;

        $this->overwrite(
            'pcntl_wtermsig',
            function () use ($expectedSignal) {
                return $expectedSignal;
            }
        );

        $status = new Status(0);
        $actualSignal = $status->getTerminateSignal();

        $this->assertEquals($expectedSignal, $actualSignal);
    }

    public function testShouldReturnIfIsExited()
    {
        $this->overwrite(
            'pcntl_wifexited',
            function () {
                return true;
            }
        );

        $status = new Status(0);

        $this->assertTrue($status->isExited());
    }

    public function testShouldReturnIfIsSignaled()
    {
        $this->overwrite(
            'pcntl_wifsignaled',
            function () {
                return true;
            }
        );

        $status = new Status(0);

        $this->assertTrue($status->isSignaled());
    }

    public function testShouldReturnIfIsStopped()
    {
        $this->overwrite(
            'pcntl_wifstopped',
            function () {
                return true;
            }
        );

        $status = new Status(0);

        $this->assertTrue($status->isStopped());
    }

    public function testShouldReturnAsSuccessfulWhenExitStatusIsZero()
    {
        $this->overwrite(
            'pcntl_wexitstatus',
            function () {
                return 0;
            }
        );

        $status = new Status(0);

        $this->assertTrue($status->isSuccessful());
    }

    public function testShouldReturnAsUnsuccessfulWhenExitStatusIsNotZero()
    {
        $this->overwrite(
            'pcntl_wexitstatus',
            function () {
                return 1;
            }
        );

        $status = new Status(0);

        $this->assertFalse($status->isSuccessful());
    }
}
