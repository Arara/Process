<?php

namespace Arara\Process;

/**
 * @covers Arara\Process\Control
 */
class ControlTest extends \TestCase
{
    public function testShouldExcecuteACommand()
    {
        $command = '/bin/date';

        $control = new Control();
        $control->execute($command);

        $this->assertEquals($command, $GLOBALS['arara']['pcntl_exec']['args'][0]);
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Error when executing command
     */
    public function testShouldThrowsAnExceptionWhenCommandExecutionFails()
    {
        $GLOBALS['arara']['pcntl_exec']['return'] = false;

        $control = new Control();
        $control->execute('/bin/date');
    }

    public function testShouldReturnPidForParentProcessWhenForking()
    {
        $GLOBALS['arara']['pcntl_fork']['return'] = 12345;

        $control = new Control();

        $this->assertEquals($GLOBALS['arara']['pcntl_fork']['return'], $control->fork());
    }

    public function testShouldReturnZeroForChildProcessWhenForking()
    {
        $GLOBALS['arara']['pcntl_fork']['return'] = 0;

        $control = new Control();

        $this->assertEquals($GLOBALS['arara']['pcntl_fork']['return'], $control->fork());
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to fork process
     */
    public function testShouldThrowsAnExceptionWhenForkFails()
    {
        $GLOBALS['arara']['pcntl_fork']['return'] = -1;

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
        $control = new Control();
        $control->signal();

        $this->assertEquals(4, $GLOBALS['arara']['pcntl_signal']['count']);
    }

    public function testShouldWaitAndReturn()
    {
        $GLOBALS['arara']['pcntl_wait']['return'] = -1;

        $control = new Control();
        $this->assertEquals(-1, $control->wait());
    }

    public function testShouldWaitAndUpdateStatus()
    {
        $GLOBALS['arara']['pcntl_wait']['status'] = 42;

        $control = new Control();
        $status = null;
        $control->wait($status);

        $this->assertEquals(42, $status);
    }

    public function testShouldWaitProcessId()
    {
        $control = new Control();
        $control->waitProcessId(999);

        $this->assertEquals(array(999, null, 0), $GLOBALS['arara']['pcntl_waitpid']['args']);
    }

    public function testShouldWaitProcessIdAndReturn()
    {
        $GLOBALS['arara']['pcntl_waitpid']['return'] = -1;

        $control = new Control();
        $this->assertEquals(-1, $control->waitProcessId(999));
    }

    public function testShouldWaitProcessIdAndUpdateStatus()
    {
        $GLOBALS['arara']['pcntl_waitpid']['status'] = 42;

        $control = new Control();
        $status = null;
        $control->waitProcessId(999, $status);

        $this->assertEquals(42, $status);
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Seconds must be a number greater than or equal to 0
     */
    public function testShouldNotAcceptANonNumericValueOnFlushMethod()
    {
        $control = new Control();
        $control->flush(array());
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Seconds must be a number greater than or equal to 0
     */
    public function testShouldNotAcceptANonPositiveValueOnFlushMethod()
    {
        $control = new Control();
        $control->flush(-1);
    }

    public function testShouldSleepSecondsWhenValueIsIntegerOnFlushMethod()
    {
        $GLOBALS['arara']['sleep']['return'] = null;
        $control = new Control();
        $control->flush(10);

        $this->assertEquals(array(10), $GLOBALS['arara']['sleep']['args']);
    }

    public function testShouldSleepMicroSecondsWhenValueIsFloatOnFlushMethod()
    {
        $GLOBALS['arara']['usleep']['return'] = null;

        $control = new Control();
        $control->flush(0.1);

        $this->assertEquals(array(100000), $GLOBALS['arara']['usleep']['args']);
    }

    public function testShouldUseZeroAsDefaultSleepValueOnFlushMethod()
    {
        $control = new Control();
        $control->flush();

        $this->assertEquals(array(0), $GLOBALS['arara']['sleep']['args']);
    }

    public function testShouldClearsFileStatusCacheOnFlushMethod()
    {
        $control = new Control();
        $control->flush();

        $this->assertEquals(1, $GLOBALS['arara']['clearstatcache']['count']);
    }

    public function testShouldCollectExistingGarbageCyclesOnFlushMethod()
    {
        $control = new Control();
        $control->flush();

        $this->assertEquals(1, $GLOBALS['arara']['gc_collect_cycles']['count']);
    }
}
