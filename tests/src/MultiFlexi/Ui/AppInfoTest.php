<?php

namespace Test\MultiFlexi\Ui;

use MultiFlexi\Ui\AppInfo;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:12.
 */
class AppInfoTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var AppInfo
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new AppInfo();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers MultiFlexi\Ui\AppInfo::afterAdd
     * @todo   Implement testafterAdd().
     */
    public function testafterAdd()
    {
        $this->assertEquals('', $this->object->afterAdd());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}