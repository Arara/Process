<?php

namespace Arara\Process\Control;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Control\Signal
 */
class SignalTest extends TestCase
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
        $signal->setHandler(SIGINT, 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGINT));
    }

    public function testShouldHandleASignalByPcntlConstantName()
    {
        $signal = new Signal();
        $signal->setHandler('SIGINT', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGINT));
    }

    public function testShouldHandleASignalByName()
    {
        $signal = new Signal();
        $signal->setHandler('alarm', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGALRM));
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The given value is not a valid signal
     */
    public function testShouldThrowsAnExceptionWhenSignalIsNotValid()
    {
        $signal = new Signal();
        $signal->setHandler('something', 'trim');
    }

    /**
     * @expectedException RuntimeException
     * @expectedExceptionMessage Could not define signal handler
     */
    public function testShouldThrowsExceptionWhenConNotRegisterHandler()
    {
        $GLOBALS['arara']['pcntl_signal']['return'] = false;

        $signal = new Signal();

        $signal->setHandler('child', 'trim');
    }

    /**
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage The given handler is not a valid callback
     */
    public function testShouldThrowsAnExceptionWhenSignalHandlerIsNotAValidCallback()
    {
        $signal = new Signal();
        $signal->setHandler('hangup', array());
    }

    public function testShouldIgnoreASignalByPcntlConstant()
    {
        $signal = new Signal();
        $signal->setHandler('SIGINT', SIG_IGN);

        $this->assertEquals(array(SIGINT, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldIgnoreASignalByPcntlConstantName()
    {
        $signal = new Signal();
        $signal->setHandler(SIGINT, SIG_IGN);

        $this->assertEquals(array(SIGINT, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldIgnoreASignalByName()
    {
        $signal = new Signal();
        $signal->setHandler('terminate', SIG_IGN);

        $this->assertEquals(array(SIGTERM, SIG_IGN), $GLOBALS['arara']['pcntl_signal']['args']);
    }

    public function testShouldThowsAnExceptionWhenSignalHandleFails()
    {
        $signal = new Signal();
        $signal->setHandler('quit', SIG_DFL);
    }

    public function testShouldSetHandler()
    {
        $signal = new Signal();
        $signal->setHandler('quit', 'rtrim');
        $signal->setHandler('quit', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers('quit'));
    }

    public function testShouldAppendHandler()
    {
        $signal = new Signal();
        $signal->appendHandler('quit', 'rtrim');
        $signal->appendHandler('quit', 'trim');
        $signal->appendHandler('quit', 'ltrim');

        $this->assertEquals(array('rtrim', 'trim', 'ltrim'), $signal->getHandlers('quit'));
    }

    public function testShouldPrependHandler()
    {
        $signal = new Signal();
        $signal->prependHandler('stop', 'rtrim');
        $signal->prependHandler('stop', 'trim');
        $signal->prependHandler('stop', 'ltrim');

        $this->assertEquals(array('ltrim', 'trim', 'rtrim'), $signal->getHandlers('stop'));
    }

    public function testShouldHandleSignals()
    {
        $count = 0;
        $callback1 = function () use (&$count) { $count++; };
        $callback2 = function () use (&$count) { $count++; };
        $callback3 = function () use (&$count) { $count++; };

        $signal = new Signal();
        $signal->prependHandler(SIGTERM, $callback1);
        $signal->prependHandler(SIGTERM, $callback2);
        $signal->prependHandler(SIGTERM, $callback3);
        $signal(SIGTERM);

        $this->assertEquals(3, $count);
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
