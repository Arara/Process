<?php

namespace Arara\Process;

function posix_getpid() { return $GLOBALS['posix_getpid']; }

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    protected function setUp()
    {
        $GLOBALS['posix_getpid'] = 1024;
    }

    protected function tearDown()
    {
        $this->setUp();
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
            ->setMethods(array('start'))
            ->disableOriginalConstructor()
            ->getMock();
        $process
            ->expects($this->once())
            ->method('start');

        $pool = $this
            ->getMockBuilder('SplObjectStorage')
            ->setMethods(array('count'))
            ->getMock();
        $pool
            ->expects($this->once())
            ->method('count')
            ->will($this->returnValue(0));

        $manager = new Manager(1);

        $reflection = new \ReflectionProperty($manager, 'pool');
        $reflection->setAccessible(true);
        $reflection->setValue($manager, $pool);

        $manager->addChild($process);
    }

    /**
     * @covers Arara\Process\Manager::addChild
     */
    public function testShouldAddAndStartChildAfterRemovingLastProcessQueueWhenNotRunningAboveTheLimit()
    {
        $first = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start', 'isRunning'))
            ->disableOriginalConstructor()
            ->getMock();
        $first
            ->expects($this->once())
            ->method('start');
        $first
            ->expects($this->once())
            ->method('isRunning')
            ->will($this->returnValue(false));

        $seccond = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start', 'wait', 'isRunning'))
            ->disableOriginalConstructor()
            ->getMock();
        $seccond
            ->expects($this->once())
            ->method('start');
        $seccond
            ->expects($this->once())
            ->method('isRunning')
            ->will($this->returnValue(true));
        $seccond
            ->expects($this->once())
            ->method('wait');

        $third = $this
            ->getMockBuilder('Arara\Process\Item')
            ->setMethods(array('start'))
            ->disableOriginalConstructor()
            ->getMock();
        $third
            ->expects($this->once())
            ->method('start');

        $manager = new Manager(1);
        $manager->addChild($first);
        $manager->addChild($seccond);
        $manager->addChild($third);
    }


}

