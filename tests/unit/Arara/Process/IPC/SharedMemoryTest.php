<?php
namespace Arara\Process\IPC;

use Arara\Test\TestCase;

/**
 * Class SharedMemoryTest
 *
 * @author Nikolay Bondarenko <misterionkell@gmail.com>
 * @version 1.0
 *
 * @small
 * @covers Arara\Process\IPC\SharedMemory
 * @covers Arara\Process\IPC\Semaphore
 */
class SharedMemoryTest extends TestCase
{
    public function testArrayAccessExists()
    {
        $sm = new SharedMemory();
        $this->assertFalse(isset($sm['test']));
    }

    public function testArrayAccessGetAndSet()
    {
        $sm = new SharedMemory();

        $sm['test'] = 'hello';
        $this->assertEquals('hello', $sm['test']);
    }

    public function testArrayAccessGetUnExisting()
    {
        $sm = new SharedMemory();
        $this->assertEmpty($sm['test']);
    }

    public function testArrayAccessDelete()
    {
        $sm = new SharedMemory();
        $sm['test'] = 'hello';

        unset($sm['test']);
        $this->assertFalse(isset($sm['test']));
    }

    public function testCountable()
    {
        $sm = new SharedMemory();

        $sm['test'] = 'value';
        $this->assertCount(1, $sm);

        unset($sm['test']);
        $this->assertCount(0, $sm);
    }
}