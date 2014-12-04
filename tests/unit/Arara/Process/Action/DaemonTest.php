<?php

namespace Arara\Process\Action;

use Arara\Process\Context;
use Arara\Process\Control;
use Arara\Process\Pidfile;
use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Action\Daemon
 */
class DaemonTest extends TestCase
{
    protected function init()
    {
        $this->overwrite(
            'fopen',
            function () {
                return 'a resource';
            }
        );
        $this->overwrite(
            'fgets',
            function () {
                return '';
            }
        );
    }

    public function testShouldDefineACallbackActionOnConstructor()
    {
        $action = function () {};
        $daemon = new Daemon($action);

        $this->assertSame($action, $daemon->getCallable());
    }

    public function testShouldDefineOptionsOnConstructor()
    {
        $action = function () {};
        $options = array(
            'name' => 'myapp',
            'lock_dir' => '/tmp',
            'work_dir' => __DIR__,
            'umask' => 2,
            'user_id' => 10001,
            'group_id' => 10001,
            'stdin' => '/dev/stdin',
            'stdout' => '/dev/stdout',
            'stderr' => '/dev/stderr',
        );

        $daemon = new Daemon($action, $options);

        $this->assertSame($options, $daemon->getOptions());
    }

    public function testShouldHaveOptionsByDefault()
    {
        $daemon = new Daemon(function () {});
        $expectedOptions = array(
            'name' => 'arara',
            'lock_dir' => '/var/run',
            'work_dir' => '/',
            'umask' => 0,
            'user_id' => null,
            'group_id' => null,
            'stdin' => '/dev/null',
            'stdout' => '/dev/null',
            'stderr' => '/dev/null',
        );

        $this->assertSame($expectedOptions, $daemon->getOptions());
    }

    public function testShouldBindDefaultEventHandlers()
    {
        $daemon = new Daemon(function () {});
        $expectedHandlers = array(
            Daemon::EVENT_INIT    => array($daemon, 'handleInit'),
            Daemon::EVENT_FORK    => array($daemon, 'handleFork'),
            Daemon::EVENT_START   => array($daemon, 'handleStart'),
        );

        $this->assertSame($expectedHandlers, $daemon->getHandlers());
    }

    public function testShouldReturnAsNotDyingByDefault()
    {
        $daemon = new Daemon(function () {});

        $this->assertFalse($daemon->isDying());
    }

    public function testShouldChangeDyingStatus()
    {
        $daemon = new Daemon(function () {});
        $daemon->setAsDying(true);

        $this->assertTrue($daemon->isDying());
    }

    public function testShouldOverwriteOptions()
    {
        $daemon = new Daemon(function () {});
        $daemon->setOption('lock_dir', '/tmp');

        $this->assertSame('/tmp', $daemon->getOption('lock_dir'));
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage "chumba" is not a valid option
     */
    public function testShouldThrowsAnExceptinoWhenDefiningANonExistingOption()
    {
        $daemon = new Daemon(function () {});
        $daemon->setOption('chumba', '\o/');
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage You can not bind a callback for this event
     */
    public function testShouldNotBeAbleToBindTriggerForInitEvent()
    {
        $daemon = new Daemon(function () {});
        $daemon->bind(Daemon::EVENT_INIT, function () {});
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage You can not bind a callback for this event
     */
    public function testShouldNotBeAbleToBindTriggerForForkEvent()
    {
        $daemon = new Daemon(function () {});
        $daemon->bind(Daemon::EVENT_FORK, function () {});
    }

    /**
     * @expectedException Arara\Process\Exception\InvalidArgumentException
     * @expectedExceptionMessage You can not bind a callback for this event
     */
    public function testShouldNotBeAbleToBindTriggerForStartEvent()
    {
        $daemon = new Daemon(function () {});
        $daemon->bind(Daemon::EVENT_FORK, function () {});
    }

    public function testShouldNotBeAbleToBindTriggersForEvents()
    {
        $counter = 0;
        $trigger = function () use (&$counter) {
            $counter++;
        };
        $daemon = new Daemon(function () {});
        $daemon->bind(Daemon::EVENT_SUCCESS, $trigger);
        $daemon->trigger(Daemon::EVENT_SUCCESS, new Control(), new Context());

        $this->assertEquals(1, $counter);
    }

    public function testShouldExecutePayloadCallbackWithArguments()
    {
        $actualArguments = array();
        $payloadCallback = function (Control $control, Context $context, Daemon $daemon) use (&$actualArguments) {
            $actualArguments = func_get_args();
        };
        $daemon = new Daemon($payloadCallback);
        $control = new Control();
        $context = new Context();

        $expectedArguments = array($control, $context, $daemon);

        $daemon->execute($control, $context);

        $this->assertSame($expectedArguments, $actualArguments);
    }

    public function testShouldDefinePidfileOnInitEventWhenNotDefinedOnContext()
    {
        $control = new Control();
        $context = new Context();
        $daemon = new Daemon(function () {}, array('lock_dir' => __DIR__));
        $daemon->trigger(Daemon::EVENT_INIT, $control, $context);

        $this->assertInstanceOf('Arara\\Process\\Pidfile', $context->pidfile);
    }

    public function testShouldDefinePidfileWithDefinedDaemonOptionsOnInitEventWhenNotDefinedOnContext()
    {
        $control = new Control();
        $context = new Context();
        $applicationName = 'mydaemon';
        $daemon = new Daemon(function () {}, array('name' => $applicationName, 'lock_dir' => __DIR__));
        $daemon->trigger(Daemon::EVENT_INIT, $control, $context);

        $this->assertEquals($applicationName, $context->pidfile->getApplicationName());
    }

    public function testShouldNotOverwriteDefinedPidfileOnInitEventWhenAlreadyDefinedOnContext()
    {
        $control = new Control();
        $context = new Context();
        $pidfile = new Pidfile($control, 'myname', __DIR__);

        $daemon = new Daemon(function () {});
        $context->pidfile = $pidfile;
        $daemon->trigger(Daemon::EVENT_INIT, $control, $context);

        $this->assertSame($pidfile, $context->pidfile);
    }

    public function testShouldDefineProcessIdOnInitEvent()
    {
        $processId = 123456;
        $control = new Control();
        $context = new Context();
        $pidfile = $this
            ->getMockBuilder('Arara\\Process\\Pidfile')
            ->disableOriginalConstructor()
            ->getMock();
        $pidfile
            ->expects($this->once())
            ->method('getProcessId')
            ->will($this->returnValue($processId));

        $daemon = new Daemon(function () {});
        $context->pidfile = $pidfile;
        $daemon->trigger(Daemon::EVENT_INIT, $control, $context);

        $this->assertSame($processId, $context->processId);
    }

    public function testShouldDefineIfIsRunningOnInitEvent()
    {
        $isRunning = true;
        $control = new Control();
        $context = new Context();
        $pidfile = $this
            ->getMockBuilder('Arara\\Process\\Pidfile')
            ->disableOriginalConstructor()
            ->getMock();
        $pidfile
            ->expects($this->once())
            ->method('isActive')
            ->will($this->returnValue($isRunning));

        $daemon = new Daemon(function () {});
        $context->pidfile = $pidfile;
        $daemon->trigger(Daemon::EVENT_INIT, $control, $context);

        $this->assertSame($isRunning, $context->isRunning);
    }

    public function testShouldSleepOnForkEvent()
    {
        $context = new Context();
        $control = $this
            ->getMockBuilder('Arara\\Process\\Control')
            ->disableOriginalConstructor()
            ->getMock();
        $control
            ->expects($this->once())
            ->method('flush')
            ->with(0.5);

        $daemon = new Daemon(function () {});
        $daemon->trigger(Daemon::EVENT_FORK, $control, $context);
    }

    /**
     * @expectedException Arara\Process\Exception\LogicException
     * @expectedExceptionMessage Pidfile is not defined
     */
    public function testShouldThrowsAnExceptionWhenPidfileIsNotDefinedOnStartEvent()
    {
        $context = new Context();
        $control = new Control();

        $daemon = new Daemon(function () {});
        $daemon->trigger(Daemon::EVENT_START, $control, $context);
    }
}
