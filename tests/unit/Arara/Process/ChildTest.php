<?php

namespace Arara\Process;

use Arara\Process\Action\Action;
use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Child
 */
class ChildTest extends TestCase
{
    private $control;
    private $context;
    private $action;

    protected function init()
    {
        $this->action = $this
            ->getMockBuilder('Arara\Process\Action\Action')
            ->disableOriginalConstructor();

        $this->control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->disableOriginalConstructor();

        $this->context = $this
            ->getMockBuilder('Arara\Process\Context')
            ->disableOriginalConstructor();

        $this->controlInfo = $this
            ->getMockBuilder('Arara\Process\Control\Info')
            ->disableOriginalConstructor();

        $this->controlSignal = $this
            ->getMockBuilder('Arara\Process\Control\Signal')
            ->disableOriginalConstructor();
    }

    protected function finish()
    {
        $this->action = null;
        $this->control = null;
        $this->context = null;
    }

    public function testShouldAcceptAnActionAndAControlOnConstructor()
    {
        $action = $this->action->getMock();
        $control = $this->control->getMock();

        $child = new Child($action, $control);

        $this->assertAttributeSame($action, 'action', $child);
        $this->assertAttributeSame($control, 'control', $child);
    }

    public function testShouldAcceptATimeoutOnConstructor()
    {
        $action = $this->action->getMock();
        $control = $this->control->getMock();
        $timeout = 10;

        $child = new Child($action, $control, $timeout);
        $context = $this->getObjectPropertyValue($child, 'context');

        $this->assertSame($timeout, $context->timeout);
    }

    public function testShouldHaveZeroAsDefaultTimeout()
    {
        $action = $this->action->getMock();
        $control = $this->control->getMock();
        $child = new Child($action, $control);
        $context = $this->getObjectPropertyValue($child, 'context');

        $this->assertSame(0, $context->timeout);
    }

    public function testShoulReturnIsHasId()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = 12345;

        $this->assertTrue($child->hasId());
    }

    public function testShoulReturnIsHasNoId()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());

        $this->assertFalse($child->hasId());
    }

    public function testShoulReturnNullAsProcessId()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = 12345;

        $this->assertEquals(12345, $child->getId());
    }

    /**
     * @expectedException Arara\Process\Exception\UnexpectedValueException
     * @expectedExceptionMessage There is no defined process identifier
     */
    public function testShouldThrowsAnExceptionWhenThereIsNoDefinedId()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());
        $child->getId();
    }

    public function testShouldReturnAsIsNotRunningWhenThereIsNoDefinedProcess()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());

        $this->assertFalse($child->isRunning());
    }

    public function testShouldReturnAsIsNotRunningWhenThereIsNoDefinedRunningProperty()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = 12345;

        $this->assertFalse($child->isRunning());
    }

    public function testShouldReturnTheRealRunningStatusWhenThereIsDefinedProcessAndRunningProperty()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(0, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertTrue($child->isRunning());
    }

    public function testShouldReturnUpdateRunningStatusPropertyAfterCheckIfProcessIsRunning()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(0, $processId)
            ->will($this->returnValue(false));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;
        $child->isRunning();

        $this->assertFalse($context->isRunning);
    }

    public function testShouldKillProcessWhenItIsRunning()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(SIGKILL, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertTrue($child->kill());
    }

    public function testShouldNotKillProcessWhenItIsNotRunning()
    {
        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->any())
            ->method('signal');

        $child = new Child($this->action->getMock(), $controlMock);

        $this->assertFalse($child->kill());
    }

    public function testShouldUpdateRunningStatusAfterKillTheProcess()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(SIGKILL, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;
        $child->kill();

        $this->assertFalse($child->isRunning());
    }

    public function testShouldTerminateProcessIfProcessIsRunning()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(SIGTERM, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertTrue($child->terminate());
    }

    public function testShouldNotTerminateProcessIfProcessIsNotRunning()
    {
        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->never())
            ->method('signal');

        $child = new Child($this->action->getMock(), $controlMock);

        $this->assertFalse($child->terminate());
    }

    public function testShouldUpdateRunningStatusAfterTerminateTheProcess()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(SIGTERM, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;
        $child->terminate();

        $this->assertFalse($child->isRunning());
    }

    public function testShouldWaitProcessIfWaitStatusNotReaped()
    {
        $processId = 123456;

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('waitProcessId')
            ->will($this->returnValue($processId));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertTrue($child->wait());
    }

    public function testShouldNotWaitWhenProcessAlreadyReaped()
    {
        $processId = 123456;

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('waitProcessId')
            ->will($this->returnValue($processId));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertTrue($child->wait()) ;

        $this->assertFalse($child->wait());
    }

    public function testShouldNotHaveAnStatusByDefault()
    {
        $child = new Child($this->action->getMock(), $this->control->getMock());

        $this->assertAttributeEmpty('status', $child);
    }

    /**
     * @depends testShouldNotHaveAnStatusByDefault
     */
    public function testShouldUpdateProcessStatusAfterWait()
    {
        $processId = 123456;

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('waitProcessId')
            ->will($this->returnValue($processId));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;
        $child->wait();

        $this->assertAttributeInstanceOf('Arara\Process\Control\Status', 'status', $child);
    }

    /**
     * @depends testShouldUpdateProcessStatusAfterWait
     */
    public function testShouldReturnCurrentProcessStatus()
    {
        $processId = 123456;

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('waitProcessId')
            ->will($this->returnValue($processId));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;

        $this->assertInstanceOf('Arara\Process\Control\Status', $child->getStatus());
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Process already started
     */
    public function testShouldNotStartActionIfItIsAlreadyRunning()
    {
        $processId = 123456;

        $controlSignalMock = $this->controlSignal
            ->setMethods(array('send'))
            ->getMock();
        $controlSignalMock
            ->expects($this->once())
            ->method('send')
            ->with(0, $processId)
            ->will($this->returnValue(true));

        $controlMock = $this->control->getMock();
        $controlMock
            ->expects($this->once())
            ->method('signal')
            ->will($this->returnValue($controlSignalMock));

        $child = new Child($this->action->getMock(), $controlMock);
        $context = $this->getObjectPropertyValue($child, 'context');
        $context->processId = $processId;
        $context->isRunning = true;
        $child->start();
    }

    public function testShouldMarkAsRunningWhenStart()
    {
        $control = $this->control->getMock();
        $control
            ->expects($this->once())
            ->method('fork')
            ->will($this->returnValue(123456));
        $child = new Child($this->action->getMock(), $control);
        $child->start();

        $context = $this->getObjectPropertyValue($child, 'context');

        $this->assertTrue($context->isRunning);
    }

    public function testShouldDefineProcessIdAfterStart()
    {
        $processId = 123456;
        $control = $this->control->getMock();
        $control
            ->expects($this->once())
            ->method('fork')
            ->will($this->returnValue($processId));
        $child = new Child($this->action->getMock(), $control);
        $child->start();

        $this->assertEquals($processId, $child->getId());
    }

    public function testShouldDefinePidForChildProcessWhenStart()
    {
        $processId = 123456;

        $controlInfo = $this->controlInfo->getMock();
        $controlInfo
            ->expects($this->once())
            ->method('getId')
            ->will($this->returnValue($processId));

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('fork')
            ->will($this->returnValue(0));
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($controlInfo));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($this->action->getMock(), $control);
        $child->start();

        $this->assertEquals($processId, $child->getId());
    }

    public function testShouldDefineTimeoutHandleWhenStart()
    {
        $signal = $this->controlSignal->getMock();
        $signal
            ->expects($this->once())
            ->method('setHandler')
            ->with('alarm');

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($signal));

        $child = new Child($this->action->getMock(), $control);
        $child->start();
    }

    public function testShouldExecuteActionWhenStart()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->once())
            ->method('execute');

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerInitEventWhenAddedToProcess()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->at(0))
            ->method('trigger')
            ->with(Action::EVENT_INIT);

        $control = $this->control->getMock();

        $child = new Child($action, $control);
    }

    public function testShouldTriggerForkEventOnParentWhenStart()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->at(1))
            ->method('trigger')
            ->with(Action::EVENT_FORK);

        $control = $this->control->getMock();
        $control
            ->expects($this->once())
            ->method('fork')
            ->will($this->returnValue(123456));
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerStartEventWhenStart()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->at(1))
            ->method('trigger')
            ->with(Action::EVENT_START);

        $control = $this->control->getMock();
        $control
            ->expects($this->once())
            ->method('fork')
            ->will($this->returnValue(0));
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerSuccessEventWhenExecuteHasNotReturnValue()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(null));
        $action
            ->expects($this->at(3))
            ->method('trigger')
            ->with(Action::EVENT_SUCCESS);

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerErrorEventWhenExecuteHasPhpErrors()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () {
                trim(array());
            }));
        $action
            ->expects($this->at(3))
            ->method('trigger')
            ->with(Action::EVENT_ERROR);

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerFailureEventWhenExecuteThrowsAnException()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->throwException(new \Exception('Whatever')));
        $action
            ->expects($this->at(3))
            ->method('trigger')
            ->with(Action::EVENT_FAILURE);

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldTriggerFinishEventAfterRunAction()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->at(4))
            ->method('trigger')
            ->with(Action::EVENT_FINISH);

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldExitAsZeroWhenExecuteHasNotReturnValue()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnValue(null));

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));
        $control
            ->expects($this->once())
            ->method('quit')
            ->with(0);

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldExitAsTwoWhenExecuteHasPhpErrors()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->returnCallback(function () {
                trim(array());
            }));

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));
        $control
            ->expects($this->once())
            ->method('quit')
            ->with(2);

        $child = new Child($action, $control);
        $child->start();
    }

    public function testShouldExitAsOneWhenExecuteThrowsAnException()
    {
        $action = $this->action->getMock();
        $action
            ->expects($this->any())
            ->method('execute')
            ->will($this->throwException(new \Exception('Whatever')));

        $control = $this->control->getMock();
        $control
            ->expects($this->any())
            ->method('info')
            ->will($this->returnValue($this->controlInfo->getMock()));
        $control
            ->expects($this->any())
            ->method('signal')
            ->will($this->returnValue($this->controlSignal->getMock()));
        $control
            ->expects($this->once())
            ->method('quit')
            ->with(1);

        $child = new Child($action, $control);
        $child->start();
    }
}
