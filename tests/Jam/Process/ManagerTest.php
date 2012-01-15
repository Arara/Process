<?php

/**
 * @namespace
 */
namespace Jam\Test\Process;

use Jam\Process as JP;

class ManagerTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Jam\Process\Manager
     */
    private $_manager;

    public function setUp()
    {
        $this->_manager = new JP\Manager();
    }

    public function tearDown()
    {
        $this->_manager = null;
    }

    /**
     * @dataProvider _providerIntegersPositives
     */
    public function testValidDefaultMaxChildrenWith($value)
    {
        JP\Manager::setDefaultMaxChildren($value);
        $this->assertEquals($value, JP\Manager::getDefaultMaxChildren());
        $this->assertEquals($value, $this->_manager->getMaxChildren());
    }

    /**
     * @dataProvider _providerIntegersPositivesNot
     * @expectedException InvalidArgumentException
     */
    public function testInvalidDefaultMaxChildrenWith($value)
    {
        JP\Manager::setDefaultMaxChildren($value);
    }

    /**
     * @dataProvider _providerIntegersPositives
     */
    public function testValidMaxChildren($value)
    {
        $this->_manager->setMaxChildren($value);
        $this->assertEquals($value, $this->_manager->getMaxChildren());
    }

    /**
     * @dataProvider _providerIntegersPositivesNot
     * @expectedException InvalidArgumentException
     */
    public function testInalidMaxChildren($value)
    {
        $this->_manager->setMaxChildren($value);
    }

    public function _providerIntegersPositives()
    {
        return include 'IntegersPositives.valid.php';
    }

    public function _providerIntegersPositivesNot()
    {
        return include 'IntegersPositives.invalid.php';
    }


}

