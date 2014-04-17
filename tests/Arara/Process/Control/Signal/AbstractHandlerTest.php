<?php

namespace Arara\Process\Control\Signal;

use Arara\Process\Control;

/**
 * @covers Arara\Process\Control\Signal\AbstractHandler
 */
class AbstractHandlerTest extends \TestCase
{
    public function testShouldAcceptAnInstanceOfControlOnConstructor()
    {
        $control = new Control();
        $handler = $this->getMockForAbstractClass('Arara\Process\Control\Signal\AbstractHandler', array($control));

        $this->assertAttributeSame($control, 'control', $handler);
    }
}
