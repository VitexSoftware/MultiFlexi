<?php

/**
 * MultiFlexi API
 * PHP version 7.4
 *
 * @package MultiFlexi
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 */

/**
 * This is an example of using OAuth2 Application Flow in a specification to describe security to your API.
 * The version of the OpenAPI document: 1.0.0
 * Generated by: https://github.com/openapitools/openapi-generator.git
 */

/**
 * NOTE: This class is auto generated by the openapi generator program.
 * https://github.com/openapitools/openapi-generator
 * Please update the test case below to test the model.
 */
namespace MultiFlexi\Model;

use PHPUnit\Framework\TestCase;
use MultiFlexi\Model\Customer;

/**
 * CustomerTest Class Doc Comment
 *
 * @package MultiFlexi\Model
 * @author  OpenAPI Generator team
 * @link    https://github.com/openapitools/openapi-generator
 *
 * @coversDefaultClass \MultiFlexi\Model\Customer
 */
class CustomerTest extends TestCase
{

    /**
     * Setup before running any test cases
     */
    public static function setUpBeforeClass(): void
    {
    }

    /**
     * Setup before running each test case
     */
    public function setUp(): void
    {
    }

    /**
     * Clean up after running each test case
     */
    public function tearDown(): void
    {
    }

    /**
     * Clean up after running all test cases
     */
    public static function tearDownAfterClass(): void
    {
    }

    /**
     * Test "Customer"
     */
    public function testCustomer()
    {
        $testCustomer = new Customer();
        $namespacedClassname = Customer::getModelsNamespace() . '\\Customer';
        $this->assertSame('\\' . Customer::class, $namespacedClassname);
        $this->assertTrue(
            class_exists($namespacedClassname),
            sprintf('Assertion failed that "%s" class exists', $namespacedClassname)
        );
        $this->markTestIncomplete(
            'Test of "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "id"
     */
    public function testPropertyId()
    {
        $this->markTestIncomplete(
            'Test of "id" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "enabled"
     */
    public function testPropertyEnabled()
    {
        $this->markTestIncomplete(
            'Test of "enabled" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "settings"
     */
    public function testPropertySettings()
    {
        $this->markTestIncomplete(
            'Test of "settings" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "email"
     */
    public function testPropertyEmail()
    {
        $this->markTestIncomplete(
            'Test of "email" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "firstname"
     */
    public function testPropertyFirstname()
    {
        $this->markTestIncomplete(
            'Test of "firstname" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "lastname"
     */
    public function testPropertyLastname()
    {
        $this->markTestIncomplete(
            'Test of "lastname" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "password"
     */
    public function testPropertyPassword()
    {
        $this->markTestIncomplete(
            'Test of "password" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "login"
     */
    public function testPropertyLogin()
    {
        $this->markTestIncomplete(
            'Test of "login" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "datCreate"
     */
    public function testPropertyDatCreate()
    {
        $this->markTestIncomplete(
            'Test of "datCreate" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test attribute "datSave"
     */
    public function testPropertyDatSave()
    {
        $this->markTestIncomplete(
            'Test of "datSave" property in "Customer" model has not been implemented yet.'
        );
    }

    /**
     * Test getOpenApiSchema static method
     * @covers ::getOpenApiSchema
     */
    public function testGetOpenApiSchema()
    {
        $schemaArr = Customer::getOpenApiSchema();
        $this->assertIsArray($schemaArr);
    }
}
