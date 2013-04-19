<?php

namespace Arara\Process;

$GLOBALS['posix_getpid'] = 1024;
$GLOBALS['pcntl_setpriority'] = true;

function posix_getpid()
{
    return $GLOBALS['posix_getpid'];
}

function pcntl_setpriority()
{
    return $GLOBALS['pcntl_setpriority'];
}

class ManagerTest extends \PHPUnit_Framework_TestCase
{


    protected function setUp()
    {
        $GLOBALS['posix_getpid'] = 1024;
        $GLOBALS['pcntl_setpriority'] = true;
    }

    /**
     * @covers Arara\Process\Manager::__construct
     * @covers Arara\Process\Manager::getMaxChildren
     */
    public function testShouldDefineAndRetrieveValidMaxChildrenNumbers()
    {
        $value = 50;
        $manager = new Manager($value);

        $this->assertEquals($value, $manager->getMaxChildren());
    }

    /**
     * @covers Arara\Process\Manager::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Children number is not valid
     */
    public function testShouldNotDefineInvalidMaxChildrenNumbers()
    {
        new Manager(new \stdClass());
    }


    /**
     * @covers Arara\Process\Manager::getPid
     */
    public function testShouldHaveAValidPidNumber()
    {
        $manager = new Manager(1);

        $this->assertSame($GLOBALS['posix_getpid'], $manager->getPid());
    }

    /**
     * @covers Arara\Process\Manager::addChild
     * @covers Arara\Process\Manager::__destruct
     */
    public function testShouldAddAndStartChildWhenNotRunningAboveTheLimit()
    {
        $process = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start', 'wait'))
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->once())
            ->method('start');
        $process
            ->expects($this->once())
            ->method('wait');

        $queue = $this
            ->getMockBuilder('Arara\Process\Queue')
            ->setMethods(array('count', 'top'))
            ->getMock();
        $queue
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));
        $queue
            ->expects($this->never())
            ->method('top');

        $manager = new Manager(1);

        $reflection = new \ReflectionProperty($manager, 'queue');
        $reflection->setAccessible(true);
        $reflection->setValue($manager, $queue);

        $manager->addChild($process);
    }

    /**
     * @covers Arara\Process\Manager::addChild
     * @covers Arara\Process\Manager::__destruct
     * @expectedException RuntimeException
     * @expectedExceptionMessage Unable to set the priority
     */
    public function testShouldThrowsAnExceptionIfCouldNotSetChildPriority()
    {
        $process = $this
            ->getMockBuilder('Arara\Process\Item')
            ->disableOriginalConstructor()
            ->getMock();

        $GLOBALS['pcntl_setpriority'] = false;
        $manager = new Manager(1);
        $manager->addChild($process);
    }

    /**
     * @covers Arara\Process\Manager::addChild
     * @covers Arara\Process\Manager::__destruct
     */
    public function testShouldAddAndStartChildAfterRemovingLastProcessQueueWhenNotRunningAboveTheLimit()
    {
        $first = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start', 'wait'))
            ->disableOriginalConstructor()
            ->getMock();
        $first
            ->expects($this->once())
            ->method('start');
        $first
            ->expects($this->exactly(2))
            ->method('wait');

        $seccond = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start', 'wait'))
            ->disableOriginalConstructor()
            ->getMock();
        $seccond
            ->expects($this->once())
            ->method('start');
        $seccond
            ->expects($this->once())
            ->method('wait');

        $queue = $this
            ->getMockBuilder('Arara\Process\Queue')
            ->setMethods(array('count', 'top'))
            ->getMock();
        $queue
            ->expects($this->at(1))
            ->method('count')
            ->will($this->returnValue(1));
        $queue
            ->expects($this->at(2))
            ->method('count')
            ->will($this->returnValue(2));
        $queue
            ->expects($this->once())
            ->method('top')
            ->will($this->returnValue($first));

        $manager = new Manager(1);

        $reflection = new \ReflectionProperty($manager, 'queue');
        $reflection->setAccessible(true);
        $reflection->setValue($manager, $queue);

        $manager->addChild($first);
        $manager->addChild($seccond);
    }


}

