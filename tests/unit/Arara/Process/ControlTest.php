<?php

namespace Arara\Process;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Control
 */
class ControlTest extends TestCase
{
    public function testShouldExcecuteACommand()
    {
        $actualCommand = null;
        $expectedCommand = '/bin/date';

        $this->overwrite(
            'pcntl_exec',
            function ($command) use (&$actualCommand) {
                $actualCommand = $command;
            }
        );

        $control = new Control();
        $control->execute($expectedCommand);

        $this->assertEquals($expectedCommand, $actualCommand);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Error when executing command
     */
    public function testShouldThrowsAnExceptionWhenCommandExecutionFails()
    {
        $this->overwrite(
            'pcntl_exec',
            function () {
                return false;
            }
        );

        $control = new Control();
        $control->execute('/bin/date');
    }

    public function testShouldReturnPidForParentProcessWhenForking()
    {
        $expectedPid = 12345;

        $this->overwrite(
            'pcntl_fork',
            function () use ($expectedPid) {
                return $expectedPid;
            }
        );

        $control = new Control();
        $actualPid = $control->fork();

        $this->assertEquals($expectedPid, $actualPid);
    }

    public function testShouldReturnZeroForChildProcessWhenForking()
    {
        $expectedReturn = 0;

        $this->overwrite(
            'pcntl_fork',
            function () use ($expectedReturn) {
                return $expectedReturn;
            }
        );

        $control = new Control();
        $actualReturn = $control->fork();

        $this->assertEquals($expectedReturn, $actualReturn);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Unable to fork process
     */
    public function testShouldThrowsAnExceptionWhenForkFails()
    {
        $this->overwrite(
            'pcntl_fork',
            function () {
                return -1;
            }
        );

        $control = new Control();
        $control->fork();
    }

    public function testShouldReturnAValidInfoController()
    {
        $control = new Control();

        $this->assertInstanceOf(__NAMESPACE__ . '\\Control\\Info', $control->info());
    }

    public function testShouldReturnAValidSignalController()
    {
        $control = new Control();

        $this->assertInstanceOf(__NAMESPACE__ . '\\Control\\Signal', $control->signal());
    }

    public function testShouldDefineSignalHandlersByDefault()
    {
        $actualCount = 0;
        $expectedCount = 4;

        $this->overwrite(
            'pcntl_signal',
            function () use (&$actualCount) {
                $actualCount++;

                return true;
            }
        );

        $control = new Control();
        $control->signal();

        $this->assertEquals($expectedCount, $actualCount);
    }

    public function testShouldWaitAndReturn()
    {
        $expectedReturn = -1;

        $this->overwrite(
            'pcntl_wait',
            function () use ($expectedReturn) {
                return $expectedReturn;
            }
        );

        $control = new Control();
        $actualReturn = $control->wait();

        $this->assertEquals($expectedReturn, $actualReturn);
    }

    public function testShouldWaitAndUpdateStatus()
    {
        $actualStatus = null;
        $expectedStatus = -1;

        $this->overwrite(
            'pcntl_wait',
            function (&$status) use ($expectedStatus) {
                $status = $expectedStatus;
            }
        );

        $control = new Control();
        $control->wait($actualStatus);

        $this->assertEquals($expectedStatus, $actualStatus);
    }

    public function testShouldWaitProcessId()
    {
        $actualProcessId = null;
        $expectedProcessId = 999;

        $this->overwrite(
            'pcntl_waitpid',
            function ($processId) use (&$actualProcessId) {
                $actualProcessId = $processId;
            }
        );

        $control = new Control();
        $control->waitProcessId(999);

        $this->assertEquals($expectedProcessId, $actualProcessId);
    }

    public function testShouldWaitProcessIdAndReturn()
    {
        $expectedReturn = -1;

        $this->overwrite(
            'pcntl_waitpid',
            function () use ($expectedReturn) {
                return $expectedReturn;
            }
        );

        $control = new Control();
        $actualReturn = $control->waitProcessId(999);

        $this->assertEquals($expectedReturn, $actualReturn);
    }

    public function testShouldWaitProcessIdAndUpdateStatus()
    {
        $actualStatus = null;
        $expectedStatus = 42;

        $this->overwrite(
            'pcntl_waitpid',
            function ($processId, &$status) use ($expectedStatus) {
                $status = $expectedStatus;
                return -1;
            }
        );

        $control = new Control();
        $control->waitProcessId(999, $actualStatus);

        $this->assertEquals($expectedStatus, $actualStatus);
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Seconds must be a number greater than or equal to 0
     */
    public function testShouldNotAcceptANonNumericValueOnFlushMethod()
    {
        $control = new Control();
        $control->flush(array());
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage Seconds must be a number greater than or equal to 0
     */
    public function testShouldNotAcceptANonPositiveValueOnFlushMethod()
    {
        $control = new Control();
        $control->flush(-1);
    }

    public function testShouldSleepSecondsWhenValueIsIntegerOnFlushMethod()
    {
        $actualTime = null;
        $expectedTime = 10;

        $this->overwrite(
            'sleep',
            function ($seconds) use (&$actualTime) {
                $actualTime = $seconds;
            }
        );

        $control = new Control();
        $control->flush($expectedTime);

        $this->assertSame($expectedTime, $actualTime);
    }

    public function testShouldSleepMicroSecondsWhenValueIsFloatOnFlushMethod()
    {
        $actualTime = null;
        $expectedTime = 0.5;

        $this->overwrite(
            'usleep',
            function ($seconds) use (&$actualTime) {
                $actualTime = $seconds / 1000000;
            }
        );

        $control = new Control();
        $control->flush($expectedTime);

        $this->assertSame($expectedTime, $actualTime);
    }

    public function testShouldUseZeroAsDefaultSleepValueOnFlushMethod()
    {
        $actualTime = null;
        $expectedTime = 0;

        $this->overwrite(
            'sleep',
            function ($seconds) use (&$actualTime) {
                $actualTime = $seconds;
            }
        );

        $control = new Control();
        $control->flush();

        $this->assertSame($expectedTime, $actualTime);
    }

    public function testShouldClearsFileStatusCacheOnFlushMethod()
    {
        $actualCount = 0;
        $expectedCount = 1;

        $this->overwrite(
            'clearstatcache',
            function () use (&$actualCount) {
                $actualCount++;
            }
        );

        $control = new Control();
        $control->flush();

        $this->assertSame($expectedCount, $actualCount);
    }

    public function testShouldCollectExistingGarbageCyclesOnFlushMethod()
    {
        $actualCount = 0;
        $expectedCount = 1;

        $this->overwrite(
            'gc_collect_cycles',
            function () use (&$actualCount) {
                $actualCount++;
            }
        );

        $control = new Control();
        $control->flush();

        $this->assertSame($expectedCount, $actualCount);
    }
}
