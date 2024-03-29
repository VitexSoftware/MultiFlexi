<?php

namespace Test\MultiFlexi\Api;

use MultiFlexi\Api\AppApi;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:34.
 */
class AppApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AppApi
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new AppApi();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers MultiFlexi\Api\AppApi::getAppById
     * @todo   Implement testgetAppById().
     */
    public function testgetAppById()
    {
        $this->assertEquals('', $this->object->getAppById());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\AppApi::listApps
     * @todo   Implement testlistApps().
     */
    public function testlistApps()
    {
        $this->assertEquals('', $this->object->listApps());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\AppApi::setAppById
     * @todo   Implement testsetAppById().
     */
    public function testsetAppById()
    {
        $this->assertEquals('', $this->object->setAppById());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
