<?php

namespace Arara\Process\Handler;

function time()
{
    if ( isset($GLOBALS['time'])) {
        return $GLOBALS['time'];
    }

    return \time();
}

use Arara\Process\Action\Action;
use Arara\Process\Context;

/**
 * @covers Arara\Process\Handler\SignalAlarm
 */
class SignalAlarmTest extends \TestCase
{

    const TIMESTAMP = 1230192830;

    protected function init()
    {
        $GLOBALS['time'] = self::TIMESTAMP;
    }

    protected function finish()
    {
        unset($GLOBALS['time']);
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
