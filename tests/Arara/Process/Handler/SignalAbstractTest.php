<?php

namespace Arara\Process\Handler;

use Arara\Process\Control;

/**
 * @covers Arara\Process\Handler\SignalAbstract
 */
class SignalAbstractTest extends \TestCase
{
    public function testShouldAcceptAnInstanceOfControlOnConstructor()
    {
        $control = new Control();
        $handler = $this->getMockForAbstractClass('Arara\Process\Handler\SignalAbstract', array($control));

        $this->assertAttributeSame($control, 'control', $handler);
    }
}
