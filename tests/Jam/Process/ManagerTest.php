<?php

namespace Jam\Process;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getValid
     * @covers Jam\Process\Manager::__construct
     * @covers Jam\Process\Manager::getMaxChildren
     */
    public function testShouldDefineAndRetrieveValidMaxChildrenNumbers($value)
    {
        $manager = new Manager($value);

        $this->assertEquals($value, $manager->getMaxChildren());
    }

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getInvalid
     * @covers Jam\Process\Manager::__construct
     * @expectedException InvalidArgumentException
     * @expectedExceptionMessage Children must be an int and greater than 1
     */
    public function testShouldNotDefineInvalidMaxChildrenNumbers($value)
    {
        $manager = new Manager($value);
    }

    /**
     * @covers Jam\Process\Manager::__construct
     * @covers Jam\Process\Manager::getMaxChildren
     */
    public function testShouldHaveFiveMaxChildrenByDefault()
    {
        $manager = new Manager();

        $this->assertEquals(5, $manager->getMaxChildren());
    }

    /**
     * @covers Jam\Process\Manager::getPid
     */
    public function testShouldHaveAValidPidNumber()
    {
        $manager = new Manager();

        $this->assertGreaterThan(0, $manager->getPid());
        $this->assertInternalType('int', $manager->getPid());
    }


}

