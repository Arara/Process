<?php

namespace Arara\Process\Handler;

use Arara\Process\Control;

/**
 * @covers Arara\Process\Handler\ErrorException
 */
class ErrorExceptionTest extends \TestCase
{
    /**
     * @expectedException ErrorException
     * @expectedExceptionMessage Some message
     */
    public function testShouldThrowsErrorExceptionWhenCalled()
    {
        $handler = new ErrorException();
        $handler(E_ERROR, 'Some message', __FILE__, __LINE__);
    }
}
