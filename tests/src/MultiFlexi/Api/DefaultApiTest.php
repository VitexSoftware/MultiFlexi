<?php

namespace Test\MultiFlexi\Api;

use MultiFlexi\Api\DefaultApi;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:35.
 */
class DefaultApiTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DefaultApi
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new DefaultApi();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
        
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::rootGet
     * @todo   Implement testrootGet().
     */
    public function testrootGet()
    {
        $this->assertEquals('', $this->object->rootGet());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::loginGet
     * @todo   Implement testloginGet().
     */
    public function testloginGet()
    {
        $this->assertEquals('', $this->object->loginGet());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::getApiIndex
     * @todo   Implement testgetApiIndex().
     */
    public function testgetApiIndex()
    {
        $this->assertEquals('', $this->object->getApiIndex());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::pingsuffixGet
     * @todo   Implement testpingsuffixGet().
     */
    public function testpingsuffixGet()
    {
        $this->assertEquals('', $this->object->pingsuffixGet());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::prepareResponse
     * @todo   Implement testprepareResponse().
     */
    public function testprepareResponse()
    {
        $this->assertEquals('', $this->object->prepareResponse());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers MultiFlexi\Api\DefaultApi::arrayToXml
     * @todo   Implement testarrayToXml().
     */
    public function testarrayToXml()
    {
        $this->assertEquals('', $this->object->arrayToXml());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}