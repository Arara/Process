<?php

namespace Arara\Process;

use Arara\Test\TestCase;

/**
 * @covers Arara\Process\Pool
 */
class PoolTest extends TestCase
{
    public function testShouldDefineProcessLimitOnConstructor()
    {
        $pool = new Pool(42);

        $this->assertAttributeSame(42, 'processLimit', $pool);
    }

    public function testShouldNotRunAutomatically()
    {
        $pool = new Pool(42);

        $this->assertFalse($pool->isRunning());
    }

    public function testShouldReturnAsRunningAfterStart()
    {
        $pool = new Pool(42);
        $pool->start();

        $this->assertTrue($pool->isRunning());
    }

    public function testShouldDefineAutoStartOnConstructor()
    {
        $pool = new Pool(42, true);

        $this->assertTrue($pool->isRunning());
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Pool is already running
     */
    public function testShouldThrowsAnExceptionWhenTryingToStartTwice()
    {
        $pool = new Pool(42, true);
        $pool->start();
        $pool->start();
    }

    public function testShouldReturnAsNotRunningAfterKill()
    {
        $pool = new Pool(42, true);
        $pool->kill();

        $this->assertFalse($pool->isRunning());
    }

    public function testShouldReturnFalseWhenTryingToKillANonRunningPool()
    {
        $pool = new Pool(42);

        $this->assertFalse($pool->kill());
    }

    public function testShouldReturnAsNotRunningAfterTerminate()
    {
        $pool = new Pool(42, true);
        $pool->terminate();

        $this->assertFalse($pool->isRunning());
    }

    public function testShouldReturnFalseWhenTryingToTerminateANonRunningPool()
    {
        $pool = new Pool(42);

        $this->assertFalse($pool->terminate());
    }

    public function testShouldAttachProcessToPool()
    {
        $pool = new Pool(1);
        $pool->attach($this->getMock('Arara\Process\Process'));
        $pool->attach($this->getMock('Arara\Process\Process'));
        $pool->attach($this->getMock('Arara\Process\Process'));

        $this->assertCount(3, $pool);
    }

    public function testShouldDetachProcessToPool()
    {
        $process = $this->getMock('Arara\Process\Process');

        $pool = new Pool(1);
        $pool->attach($process);
        $pool->detach($process);

        $this->assertCount(0, $pool);
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not attach child to non-running pool
     */
    public function testShouldThrowsAnExceptionWhenTryingToAttachProcessAfterKillPool()
    {
        $pool = new Pool(1, true);
        $pool->kill();
        $pool->attach($this->getMock('Arara\Process\Process'));
    }

    /**
     * @expectedException Arara\Process\Exception\RuntimeException
     * @expectedExceptionMessage Could not attach child to non-running pool
     */
    public function testShouldThrowsAnExceptionWhenTryingToAttachProcessAfterTerminatePool()
    {
        $pool = new Pool(1, true);
        $pool->terminate();
        $pool->attach($this->getMock('Arara\Process\Process'));
    }

    public function testShouldGetTheFirstProcessAtThePool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process2 = $this->getMock('Arara\Process\Process');
        $process3 = $this->getMock('Arara\Process\Process');

        $pool = new Pool(1);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->attach($process3);

        $this->assertSame($process1, $pool->getFirstProcess());
    }

    public function testShouldStartAllProcessWhenStartPool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->once())
            ->method('start');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->once())
            ->method('start');

        $pool = new Pool(2);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->start();
    }

    public function testSholdWaitPreviousProcessWhenPoolReachedTheLimitAfterStart()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->once())
            ->method('wait');

        $process2 = $this->getMock('Arara\Process\Process');

        $pool = new Pool(1);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->start();
    }

    public function testShouldNotStartProcessAutomaticallyWhenPoolIsNotRunning()
    {
        $process = $this->getMock('Arara\Process\Process');
        $process
            ->expects($this->never())
            ->method('start');

        $pool = new Pool(1);
        $pool->attach($process);
    }

    public function testShouldStartProcessAutomaticallyWhenPoolIsRunning()
    {
        $process = $this->getMock('Arara\Process\Process');
        $process
            ->expects($this->once())
            ->method('start');

        $pool = new Pool(1, true);
        $pool->attach($process);
    }

    public function testShouldWaitFirstProcessOnARunningPoolWhenAttachingAProcessAndPoolReachedTheProcessLimit()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->once())
            ->method('wait');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $process3 = $this->getMock('Arara\Process\Process');
        $process3
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->attach($process3);
    }

    public function testShouldDetachFirstProcessOnARunningPoolWhenAttachingAProcessAndPoolReachedTheProcessLimit()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $process3 = $this->getMock('Arara\Process\Process');
        $process3
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->attach($process3);

        $this->assertCount(2, $pool);
    }

    public function testShouldRemoveNonRunningProcessWhenGettingFirstChild()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(false));

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->once())
            ->method('wait');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $process3 = $this->getMock('Arara\Process\Process');
        $process3
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->attach($process3);
        $pool->attach($this->getMock('Arara\Process\Process'));
    }

    public function testShouldKillProcessedInThePoolWhenTryingToKillPool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process1
            ->expects($this->once())
            ->method('kill');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process2
            ->expects($this->once())
            ->method('kill');

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->kill();
    }

    public function testShouldNotKillNonRunningProcessAtThePoolWhenTryingToKillPool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->onConsecutiveCalls(true, false));
        $process1
            ->expects($this->never())
            ->method('kill');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process2
            ->expects($this->once())
            ->method('kill');

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->kill();
    }

    public function testShouldTerminateProcessAtThePoolWhenTryingToTerminatePool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process1
            ->expects($this->once())
            ->method('terminate');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process2
            ->expects($this->once())
            ->method('terminate');

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->terminate();
    }

    public function testShouldNotTerminateNonRunningProcessAtThePoolWhenTryingToTerminatePool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->onConsecutiveCalls(true, false));
        $process1
            ->expects($this->never())
            ->method('terminate');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process2
            ->expects($this->once())
            ->method('terminate');

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->terminate();
    }

    public function testShouldReturnFalseWhenTryingToWaitANonRunningPool()
    {
        $pool = new Pool(42);

        $this->assertFalse($pool->wait());
    }

    public function testShouldWaitProcessAtThePoolWhenTryingToWaitPool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process1
            ->expects($this->once())
            ->method('wait');

        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->any())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $process2
            ->expects($this->once())
            ->method('wait');

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        $pool->wait();
    }

    public function testShouldNotWaitReapedProcessAtThePoolWhenTryingToWaitPool()
    {
        $process1 = $this->getMock('Arara\Process\Process');
        $process1
            ->expects($this->any())
            ->method('wait')
            ->will($this->onConsecutiveCalls(true, false));
 
        $process2 = $this->getMock('Arara\Process\Process');
        $process2
            ->expects($this->once())
            ->method('wait')
            ->will($this->returnValue(true)) ;

        $pool = new Pool(2, true);
        $pool->attach($process1);
        $pool->attach($process2);
        
        $process1->wait() ;
        
        $pool->wait();
    }
}
