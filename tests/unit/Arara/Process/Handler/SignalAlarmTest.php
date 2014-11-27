<?php

namespace Arara\Process\Handler;

use Arara\Process\Action\Action;
use Arara\Process\Context;
use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Handler\SignalAlarm
 */
class SignalAlarmTest extends TestCase
{
    const TIMESTAMP = 1230192830;

    protected function init()
    {
        $timestamp = self::TIMESTAMP;
        $this->overwrite('time', function () use ($timestamp) {
            return $timestamp;
        });
    }

    public function testShouldHandleAlarm()
    {
        $context = new Context();

        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('quit'))
            ->getMock();

        $control
            ->expects($this->once())
            ->method('quit')
            ->with(3);

        $action = $this
            ->getMockBuilder('Arara\Process\Action\Action')
            ->setMethods(array('execute', 'trigger'))
            ->getMock();

        $action
            ->expects($this->once())
            ->method('trigger')
            ->with(Action::EVENT_TIMEOUT, $control, $context);

        $handler = new SignalAlarm($control, $action, $context);
        $handler(SIGALRM);
    }

    public function testShouldUpdateContext()
    {
        $context = new Context();

        $control = $this
            ->getMockBuilder('Arara\Process\Control')
            ->setMethods(array('quit'))
            ->getMock();

        $action = $this
            ->getMockBuilder('Arara\Process\Action\Action')
            ->setMethods(array('execute', 'trigger'))
            ->getMock();

        $handler = new SignalAlarm($control, $action, $context);
        $handler(SIGALRM);

        $actualData = $context->toArray();
        $expectedData = array(
            'exitCode' => 3,
            'finishTime' => self::TIMESTAMP,
        );

        $this->assertEquals($expectedData, $actualData);
    }
}
