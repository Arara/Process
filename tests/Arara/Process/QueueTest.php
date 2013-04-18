<?php

namespace Arara\Process;

class QueueTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @covers Arara\Process\Queue::insert
     */
    public function testShouldInsertInstancesOfProcess()
    {
        $process = $this
            ->getMockBuilder('Arara\Process\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $queue = new Queue();
        $queue->insert($process, 1);

        $this->assertSame($queue->top(), $process);
    }

    /**
     * @covers Arara\Process\Queue::insert
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage You must insert only process items
     */
    public function testShouldThrowsAnExceptionWhenInsertingNotInstancesOsProcess()
    {
        $queue = new Queue();
        $queue->insert(new \stdClass(), 1);
    }

}
