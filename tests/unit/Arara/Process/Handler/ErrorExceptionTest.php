<?php

namespace Arara\Process\Handler;

use Arara\Process\Control;
use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Handler\ErrorException
 */
class ErrorExceptionTest extends TestCase
{
    /**
     * @expectedException Arara\Process\Exception\ErrorException
     * @expectedExceptionMessage Some message
     */
    public function testShouldThrowsErrorExceptionWhenCalled()
    {
        $handler = new ErrorException();
        $handler(E_ERROR, 'Some message', __FILE__, __LINE__);
    }
}
