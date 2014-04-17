<?php

namespace Arara\Process\Control;

/**
 * @covers Arara\Process\Control\Signal
 */
class SignalTest extends \TestCase
{
    public function testShouldDefineAProcessAlarm()
    {
        $alarm = 990;
        $signal = new Signal();
        $signal->alarm($alarm);

        $this->assertEquals($alarm, $GLOBALS['arara']['pcntl_alarm']['args'][0]);
    }

    public function testShouldReturnTheLastAlarmWhenDefiningANewOne()
    {
        $GLOBALS['arara']['pcntl_alarm']['return'] = 42;
        $signal = new Signal();

        $this->assertEquals(42, $signal->alarm(123));
    }

    public function testShouldReturnSignalDispatchingStatus()
    {
        $GLOBALS['arara']['pcntl_signal_dispatch']['return'] = true;
        $signal = new Signal();

        $this->assertTrue($signal->dispatch());
    }

    public function testShouldHandleASignalByPcntlConstant()
    {
        $signal = new Signal();
        $signal->handle(SIGINT, 'trim');

        $this->assertEquals(array(SIGINT, 'trim'), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldHandleASignalByPcntlConstantName()
    {
        $signal = new Signal();
        $signal->handle('SIGINT', 'trim');

        $this->assertEquals(array(SIGINT, 'trim'), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldHandleASignalByName()
    {
        $signal = new Signal();
        $signal->handle('alarm', 'trim');

        $this->assertEquals(array(SIGALRM, 'trim'), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldReturnSignalHandlingStatus()
    {
        $GLOBALS['arara']['pcntl_signal']['return'] = true;

        $signal = new Signal();

        $this->assertTrue($signal->handle('child', 'trim'));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The given handler is not a valid callback
     */
    public function testShouldThrowsAnExceptionWhenSignalHandlerIsNotAValidCallback()
    {
        $signal = new Signal();
        $signal->handle('hangup', array());
    }

    public function testShouldIgnoreASignalByPcntlConstant()
    {
        $signal = new Signal();
        $signal->ignore(SIGINT);

        $this->assertEquals(array(SIGINT, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldIgnoreASignalByPcntlConstantName()
    {
        $signal = new Signal();
        $signal->ignore('SIGINT');

        $this->assertEquals(array(SIGINT, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldIgnoreASignalByName()
    {
        $signal = new Signal();
        $signal->ignore('terminate');

        $this->assertEquals(array(SIGTERM, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldReturnSignalIgnoringStatus()
    {
        $GLOBALS['arara']['pcntl_signal']['return'] = true;

        $signal = new Signal();

        $this->assertTrue($signal->ignore('quit'));
    }

    public function testShouldSendASignalByPcntlConstant()
    {
        $signal = new Signal();
        $signal->send(SIGINT, 12345);

        $this->assertEquals(array(12345, SIGINT), $GLOBALS['arara']['posix_kill']['args']);
    }

    public function testShouldSendASignalByPcntlConstantName()
    {
        $signal = new Signal();
        $signal->send('SIGINT', 12345);

        $this->assertEquals(array(12345, SIGINT), $GLOBALS['arara']['posix_kill']['args']);
    }

    public function testShouldSendASignalByName()
    {
        $signal = new Signal();
        $signal->send('alarm', 12345);

        $this->assertEquals(array(12345, SIGALRM), $GLOBALS['arara']['posix_kill']['args']);
    }

    public function testShouldReturnSignalSendingStatus()
    {
        $GLOBALS['arara']['posix_kill']['return'] = true;

        $signal = new Signal();

        $this->assertTrue($signal->send('child', 12345));
    }

    public function testShouldUseCurrentProcessIdWhenSendingSignalWithoutDefiningProcessId()
    {
        $GLOBALS['arara']['posix_getpid']['return'] = 42;

        $signal = new Signal();
        $signal->send('kill');

        $this->assertEquals(array(42, SIGKILL), $GLOBALS['arara']['posix_kill']['args']);
    }
}
