<?php

namespace Test\MultiFlexi;

use MultiFlexi\Configuration;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:33.
 */
class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Configuration
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Configuration();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers MultiFlexi\Configuration::getName
     * @todo   Implement testgetName().
     */
    public function testgetName()
    {
        $this->assertEquals('', $this->object->getName());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Configuration::saveToSQL
     * @todo   Implement testsaveToSQL().
     */
    public function testsaveToSQL()
    {
        $this->assertEquals('', $this->object->saveToSQL());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Configuration::takeData
     * @todo   Implement testtakeData().
     */
    public function testtakeData()
    {
        $this->assertEquals('', $this->object->takeData());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Configuration::setEnvironment
     * @todo   Implement testsetEnvironment().
     */
    public function testsetEnvironment()
    {
        $this->assertEquals('', $this->object->setEnvironment());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Configuration::getAppConfig
     * @todo   Implement testgetAppConfig().
     */
    public function testgetAppConfig()
    {
        $this->assertEquals('', $this->object->getAppConfig());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}