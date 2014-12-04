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
        $actualAlarm = null;
        $expectedAlarm = 990;

        $this->overwrite(
            'pcntl_alarm',
            function ($alarm) use (&$actualAlarm) {
                $actualAlarm = $alarm;
            }
        );

        $signal = new Signal();
        $signal->alarm($expectedAlarm);

        $this->assertEquals($expectedAlarm, $actualAlarm);
    }

    public function testShouldReturnTheLastAlarmWhenDefiningANewOne()
    {
        $expectedReturn = 42;

        $this->overwrite(
            'pcntl_alarm',
            function () use ($expectedReturn) {
                return $expectedReturn;
            }
        );

        $signal = new Signal();
        $actualReturn = $signal->alarm(123);

        $this->assertEquals($expectedReturn, $actualReturn);
    }

    public function testShouldReturnSignalDispatchingStatus()
    {
        $this->overwrite(
            'pcntl_signal_dispatch',
            function () {
                return true;
            }
        );

        $signal = new Signal();

        $this->assertTrue($signal->dispatch());
    }

    public function testShouldHandleASignalByPcntlConstant()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler(SIGINT, 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGINT));
    }

    public function testShouldHandleASignalByPcntlConstantName()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler('SIGINT', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGINT));
    }

    public function testShouldHandleASignalByName()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler('alarm', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers(SIGALRM));
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage The given value is not a valid signal
     */
    public function testShouldThrowsAnExceptionWhenSignalIsNotValid()
    {
        $signal = new Signal();
        $signal->setHandler('something', 'trim');
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not define signal handler
     */
    public function testShouldThrowsExceptionWhenConNotRegisterHandler()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return false;
            }
        );

        $signal = new Signal();

        $signal->setHandler('child', 'trim');
    }

    /**
     * @expectedException PHPUnit_Framework_Exception
     * @expectedExceptionMessage Argument 2 passed to Arara\Process\Control\Signal::placeHandler() must be callable, array given
     */
    public function testShouldTriggerAnErrorWhenSignalHandlerIsNotAValidCallback()
    {
        $signal = new Signal();
        $signal->setHandler('hangup', array());
    }

    public function testShouldIgnoreASignalByPcntlConstant()
    {
        $actualArguments = null;
        $expectedArguments = array(SIGINT, SIG_IGN);

        $this->overwrite(
            'pcntl_signal',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler('SIGINT', SIG_IGN);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldIgnoreASignalByPcntlConstantName()
    {
        $actualArguments = null;
        $expectedArguments = array(SIGINT, SIG_IGN);

        $this->overwrite(
            'pcntl_signal',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler(SIGINT, SIG_IGN);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldIgnoreASignalByName()
    {
        $actualArguments = null;
        $expectedArguments = array(SIGTERM, SIG_IGN);

        $this->overwrite(
            'pcntl_signal',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler('terminate', SIG_IGN);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldSetHandler()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->setHandler('quit', 'rtrim');
        $signal->setHandler('quit', 'trim');

        $this->assertEquals(array('trim'), $signal->getHandlers('quit'));
    }

    public function testShouldAppendHandler()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->appendHandler('quit', 'rtrim');
        $signal->appendHandler('quit', 'trim');
        $signal->appendHandler('quit', 'ltrim');

        $this->assertEquals(array('rtrim', 'trim', 'ltrim'), $signal->getHandlers('quit'));
    }

    public function testShouldPrependHandler()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

        $signal = new Signal();
        $signal->prependHandler('stop', 'rtrim');
        $signal->prependHandler('stop', 'trim');
        $signal->prependHandler('stop', 'ltrim');

        $this->assertEquals(array('ltrim', 'trim', 'rtrim'), $signal->getHandlers('stop'));
    }

    public function testShouldHandleSignals()
    {
        $this->overwrite(
            'pcntl_signal',
            function () {
                return true;
            }
        );

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
        $actualArguments = null;
        $expectedArguments = array(12345, SIGINT);

        $this->overwrite(
            'posix_kill',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->send(SIGINT, 12345);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldSendASignalByPcntlConstantName()
    {
        $actualArguments = null;
        $expectedArguments = array(12345, SIGINT);

        $this->overwrite(
            'posix_kill',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->send('SIGINT', 12345);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldSendASignalByName()
    {
        $actualArguments = null;
        $expectedArguments = array(12345, SIGALRM);

        $this->overwrite(
            'posix_kill',
            function () use (&$actualArguments) {
                $actualArguments = func_get_args();

                return true;
            }
        );

        $signal = new Signal();
        $signal->send('alarm', 12345);

        $this->assertEquals($expectedArguments, $actualArguments);
    }

    public function testShouldReturnSignalSendingStatus()
    {
        $this->overwrite(
            'posix_kill',
            function () {
                return true;
            }
        );

        $signal = new Signal();

        $this->assertTrue($signal->send('child', 12345));
    }

    public function testShouldUseCurrentProcessIdWhenSendingSignalWithoutDefiningProcessId()
    {
        $actualProcessId = null;
        $expectedProcessId = 42;

        $this->overwrite(
            'posix_getpid',
            function () use ($expectedProcessId) {
                return $expectedProcessId;
            }
        );

        $this->overwrite(
            'posix_kill',
            function ($processId) use (&$actualProcessId) {
                $actualProcessId = $processId;

                return true;
            }
        );

        $signal = new Signal();
        $signal->send('kill');

        $this->assertEquals($expectedProcessId, $actualProcessId);
    }
}
