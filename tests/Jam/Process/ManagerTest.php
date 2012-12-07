<?php

/**
 * @namespace
 */
namespace Jam\Test\Process;

use Jam;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Jam\Process\Manager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new Jam\Process\Manager();
    }

    public function tearDown()
    {
        $this->manager = null;
    }

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getValid
     */
    public function testValidDefaultMaxChildrenWith($value)
    {
        Jam\Process\Manager::setDefaultMaxChildren($value);
        $this->assertEquals($value, Jam\Process\Manager::getDefaultMaxChildren());
        $this->assertEquals($value, $this->manager->getMaxChildren());
    }

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getInvalid
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDefaultMaxChildrenWith($value)
    {
        Jam\Process\Manager::setDefaultMaxChildren($value);
    }

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getValid
     */
    public function testValidMaxChildren($value)
    {
        $this->manager->setMaxChildren($value);
        $this->assertEquals($value, $this->manager->getMaxChildren());
    }

    /**
     * @dataProvider Jam\DataProvider\IntegersPositives::getInvalid
     * @expectedException InvalidArgumentException
     */
    public function testInalidMaxChildren($value)
    {
        $this->manager->setMaxChildren($value);
    }

}

