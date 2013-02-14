<?php

namespace Arara\Process;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

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
     * @expectedExceptionMessage Children must be an int and greater than 1
     */
    public function testShouldNotDefineInvalidMaxChildrenNumbers()
    {
        $value = new \stdClass();
        $manager = new Manager($value);
    }

    /**
     * @covers Arara\Process\Manager::__construct
     * @covers Arara\Process\Manager::getMaxChildren
     */
    public function testShouldHaveFiveMaxChildrenByDefault()
    {
        $manager = new Manager();

        $this->assertEquals(5, $manager->getMaxChildren());
    }

    /**
     * @covers Arara\Process\Manager::getPid
     */
    public function testShouldHaveAValidPidNumber()
    {
        $manager = new Manager();

        $this->assertGreaterThan(0, $manager->getPid());
        $this->assertInternalType('int', $manager->getPid());
    }


}

