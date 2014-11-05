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
            ->with(Action::EVENT_TIMEOUT, $control, array('finishTime' => self::TIMESTAMP));

        $handler = new SignalAlarm($control, $action, array());
        $handler(SIGALRM);
    }
}
