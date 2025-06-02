<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\MultiFlexi;

use MultiFlexi\ConfigField;
use MultiFlexi\ConfigFieldWithHelper;
use PHPUnit\Framework\TestCase;

/**
 * Tests for ConfigFieldWithHelper class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.com>
 */
class ConfigFieldWithHelperTest extends TestCase
{
    protected ConfigFieldWithHelper $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new ConfigFieldWithHelper(
            'test_field',
            'string',
            'Test Field',
            'A test field with helper',
            'Enter test value',
            'default value',
        );
    }

    /**
     * Test constructor and parent class integration.
     */
    public function testConstructor(): void
    {
        // Test that the object was initialized correctly with parameters
        $this->assertEquals('test_field', $this->object->getCode());
        $this->assertEquals('string', $this->object->getType());
        $this->assertEquals('Test Field', $this->object->getName());
        $this->assertEquals('A test field with helper', $this->object->getDescription());
        $this->assertEquals('Enter test value', $this->object->getHint());
        $this->assertEquals('default value', $this->object->getValue());

        // Test that it inherits from ConfigField
        $this->assertInstanceOf(ConfigField::class, $this->object);
    }

    /**
     * Test helper functionality - setHelper and getHelper methods.
     */
    public function testHelperMethods(): void
    {
        // Initial value should be empty string
        $this->assertEquals('', $this->object->getHelper());

        // Set a helper name
        $result = $this->object->setHelper('username_helper');

        // Test method chaining
        $this->assertSame($this->object, $result);

        // Test that the helper was set correctly
        $this->assertEquals('username_helper', $this->object->getHelper());
    }

    /**
     * Test the getData method which should return the field data as an array.
     */
    public function testGetData(): void
    {
        // Set a helper first
        $this->object->setHelper('test_helper');

        // Get the data array
        $data = $this->object->getData();

        // Test that the data includes all expected fields from parent class
        $this->assertIsArray($data);
        $this->assertEquals('test_field', $data['keyname']);
        $this->assertEquals('string', $data['type']);
        $this->assertEquals('Test Field', $data['name']);
        $this->assertEquals('A test field with helper', $data['description']);
        $this->assertEquals('Enter test value', $data['hint']);
        $this->assertEquals('default value', $data['value']);

        // Test that the helper field is included
        $this->assertArrayHasKey('helper', $data);
        $this->assertEquals('test_helper', $data['helper']);
    }

    /**
     * Test the setDataValue and getDataValue methods for manipulating field data.
     */
    public function testDataValueMethods(): void
    {
        // Test setting a data value
        $result = $this->object->setDataValue('custom_field', 'custom_value');

        // Test method return value
        $this->assertTrue($result);

        // Test retrieving the data value
        $value = $this->object->getDataValue('custom_field');
        $this->assertEquals('custom_value', $value);

        // Test retrieving a non-existent value
        $nonExistentValue = $this->object->getDataValue('non_existent_field');
        $this->assertNull($nonExistentValue);
    }

    /**
     * Test interaction with parent class methods.
     */
    public function testParentClassInteraction(): void
    {
        // Test that parent class methods still work correctly
        $this->object->setRequired(true);
        $this->assertTrue($this->object->isRequired());

        $this->object->setSecret(true);
        $this->assertTrue($this->object->isSecret());

        $this->object->setMultiLine(true);
        $this->assertTrue($this->object->isMultiLine());

        $this->object->setManual(false);
        $this->assertFalse($this->object->isManual());

        $this->object->setDefaultValue('new default');
        $this->assertEquals('new default', $this->object->getDefaultValue());
    }

    /**
     * Test with different field types.
     */
    public function testWithDifferentFieldTypes(): void
    {
        // Create objects with different field types
        $passwordField = new ConfigFieldWithHelper('password_field', 'password', 'Password', 'Enter password');
        $passwordField->setHelper('password_helper');
        $passwordField->setSecret(true);

        $this->assertEquals('password', $passwordField->getType());
        $this->assertEquals('password_helper', $passwordField->getHelper());
        $this->assertTrue($passwordField->isSecret());

        $emailField = new ConfigFieldWithHelper('email_field', 'email', 'Email', 'Enter email');
        $emailField->setHelper('email_helper');

        $this->assertEquals('email', $emailField->getType());
        $this->assertEquals('email_helper', $emailField->getHelper());
    }

    /**
     * Test with recordkey trait functionality.
     */
    public function testRecordKeyTrait(): void
    {
        // Set the primary key
        $this->object->setMyKey(123);

        // Test that the key was set correctly
        $this->assertEquals(123, $this->object->getMyKey());
    }

    /**
     * Test error handling for invalid field types.
     */
    public function testInvalidFieldType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new ConfigFieldWithHelper('test_field', 'invalid_type', 'Test Field');
    }

    /**
     * Test with edge cases.
     */
    public function testEdgeCases(): void
    {
        // Test with empty strings
        $emptyField = new ConfigFieldWithHelper('', 'string', '', '', '');
        $this->assertEquals('', $emptyField->getCode());
        $this->assertEquals('', $emptyField->getName());

        // Test with null value
        $nullValueField = new ConfigFieldWithHelper('null_field', 'string', 'Null Field', 'Field with null value', '', null);
        $this->assertNull($nullValueField->getValue());

        // Test with special characters
        $specialCharsField = new ConfigFieldWithHelper('special_chars', 'string', 'Special!@#$%^&*()', 'Test <script>alert("xss")</script>', 'Hint with <html> tags');
        $this->assertEquals('Special!@#$%^&*()', $specialCharsField->getName());
        $this->assertEquals('Test <script>alert("xss")</script>', $specialCharsField->getDescription());
        $this->assertEquals('Hint with <html> tags', $specialCharsField->getHint());
    }

    /**
     * Test with multiple data values.
     */
    public function testMultipleDataValues(): void
    {
        // Set multiple data values
        $this->object->setDataValue('field1', 'value1');
        $this->object->setDataValue('field2', 'value2');
        $this->object->setDataValue('field3', 'value3');

        // Test retrieving all values
        $this->assertEquals('value1', $this->object->getDataValue('field1'));
        $this->assertEquals('value2', $this->object->getDataValue('field2'));
        $this->assertEquals('value3', $this->object->getDataValue('field3'));

        // Test overwriting a value
        $this->object->setDataValue('field2', 'new_value2');
        $this->assertEquals('new_value2', $this->object->getDataValue('field2'));
    }

    /**
     * Test setting complex data structures as values.
     */
    public function testComplexDataValues(): void
    {
        // Test with an array value
        $arrayValue = ['key1' => 'value1', 'key2' => 'value2'];
        $this->object->setDataValue('array_field', $arrayValue);
        $this->assertEquals($arrayValue, $this->object->getDataValue('array_field'));

        // Test with an object value
        $objectValue = new \stdClass();
        $objectValue->property = 'value';
        $this->object->setDataValue('object_field', $objectValue);
        $this->assertEquals($objectValue, $this->object->getDataValue('object_field'));

        // Test with a numeric value
        $this->object->setDataValue('number_field', 123);
        $this->assertEquals(123, $this->object->getDataValue('number_field'));

        // Test with a boolean value
        $this->object->setDataValue('bool_field', true);
        $this->assertTrue($this->object->getDataValue('bool_field'));
    }
}
