<?php

declare(strict_types=1);

namespace Tests\MultiFlexi;

use MultiFlexi\ConfigField;
use MultiFlexi\ConfigFields;
use PHPUnit\Framework\TestCase;

/**
 * Test case for ConfigFields class
 */
class ConfigFieldsTest extends TestCase
{
    /**
     * Test basic instantiation
     */
    public function testConstructor(): void
    {
        $configFields = new ConfigFields();
        $this->assertInstanceOf(ConfigFields::class, $configFields);
        $this->assertEmpty($configFields->getFields());
        $this->assertEquals('', $configFields->getName());

        $namedConfigFields = new ConfigFields('test_config');
        $this->assertEquals('test_config', $namedConfigFields->getName());
    }

    /**
     * Test adding a field to the collection
     */
    public function testAddField(): void
    {
        $configFields = new ConfigFields();
        $configField = new ConfigField('test_field', 'text', 'Test Field');
        $configFields->addField($configField);

        $this->assertCount(1, $configFields->getFields());
        $this->assertSame($configField, $configFields->getField('test_field'));
    }

    /**
     * Test retrieval methods
     */
    public function testFieldRetrieval(): void
    {
        $configFields = new ConfigFields('test_source');
        
        $field1 = new ConfigField('field1', 'text', 'Field 1');
        $field2 = new ConfigField('field2', 'number', 'Field 2');
        $field3 = new ConfigField('field3', 'boolean', 'Field 3');
        
        $configFields->addField($field1)
                    ->addField($field2)
                    ->addField($field3);

        // Test getField
        $this->assertSame($field1, $configFields->getField('field1'));
        $this->assertSame($field2, $configFields->getField('field2'));
        $this->assertSame($field3, $configFields->getField('field3'));
        $this->assertNull($configFields->getField('non_existent'));

        // Test getFieldByCode
        $retrievedField = $configFields->getFieldByCode('field1');
        $this->assertSame($field1, $retrievedField);
        $this->assertEquals('test_source', $retrievedField->getSource());

        // Test with field that already has a source
        $fieldWithSource = new ConfigField('field4', 'text', 'Field 4');
        $fieldWithSource->setSource('original_source');
        $configFields->addField($fieldWithSource);
        
        $retrievedFieldWithSource = $configFields->getFieldByCode('field4');
        $this->assertEquals('original_source', $retrievedFieldWithSource->getSource());
    }

    /**
     * Test getEnvArray method
     */
    public function testGetEnvArray(): void
    {
        $configFields = new ConfigFields();
        
        $field1 = new ConfigField('field1', 'text', 'Field 1');
        $field1->setValue('value1');
        
        $field2 = new ConfigField('field2', 'number', 'Field 2');
        $field2->setValue('42');
        
        $configFields->addField($field1)
                    ->addField($field2);

        $envArray = $configFields->getEnvArray();
        $this->assertIsArray($envArray);
        $this->assertCount(2, $envArray);
        $this->assertEquals('value1', $envArray['field1']);
        $this->assertEquals('42', $envArray['field2']);
    }

    /**
     * Test the iterator implementation
     */
    public function testIterator(): void
    {
        $configFields = new ConfigFields();
        
        $field1 = new ConfigField('a_field', 'text', 'A Field');
        $field2 = new ConfigField('b_field', 'number', 'B Field');
        $field3 = new ConfigField('c_field', 'boolean', 'C Field');
        
        $configFields->addField($field1)
                    ->addField($field2)
                    ->addField($field3);

        // Test that we can iterate and fields are sorted by key
        $iterated = [];
        foreach ($configFields as $key => $field) {
            $iterated[$key] = $field;
        }
        
        $this->assertCount(3, $iterated);
        $this->assertSame($field1, $iterated['a_field']);
        $this->assertSame($field2, $iterated['b_field']);
        $this->assertSame($field3, $iterated['c_field']);
        
        // Test ordering (should be alphabetical by code due to ksort)
        $keys = array_keys($iterated);
        $this->assertEquals(['a_field', 'b_field', 'c_field'], $keys);
    }

    /**
     * Test addFields method
     */
    public function testAddFields(): void
    {
        $configFields1 = new ConfigFields('set1');
        $configFields2 = new ConfigFields('set2');
        
        $field1 = new ConfigField('field1', 'text', 'Field 1');
        $field2 = new ConfigField('field2', 'number', 'Field 2');
        
        $configFields1->addField($field1);
        $configFields2->addField($field2);
        
        $configFields1->addFields($configFields2);
        
        $this->assertCount(2, $configFields1->getFields());
        $this->assertSame($field1, $configFields1->getField('field1'));
        $this->assertSame($field2, $configFields1->getField('field2'));
    }

    /**
     * Test getFieldNames method
     */
    public function testGetFieldNames(): void
    {
        $configFields = new ConfigFields();
        
        $field1 = new ConfigField('field1', 'text', 'Field 1');
        $field2 = new ConfigField('field2', 'number', 'Field 2');
        $field3 = new ConfigField('field3', 'boolean', 'Field 3');
        
        $configFields->addField($field1)
                    ->addField($field2)
                    ->addField($field3);

        $fieldNames = $configFields->getFieldNames();
        $this->assertIsArray($fieldNames);
        $this->assertCount(3, $fieldNames);
        $this->assertEquals(['field1', 'field2', 'field3'], $fieldNames);
    }

    /**
     * Test setName and getName methods
     */
    public function testNameAccessors(): void
    {
        $configFields = new ConfigFields();
        $this->assertEquals('', $configFields->getName());
        
        $configFields->setName('new_name');
        $this->assertEquals('new_name', $configFields->getName());
    }
}

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

namespace Test\MultiFlexi;

use MultiFlexi\ConfigField;
use MultiFlexi\ConfigFields;

/**
 * Tests for MultiFlexi\ConfigFields.
 */
class ConfigFieldsTest extends \PHPUnit\Framework\TestCase
{
    protected ConfigFields $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new ConfigFields();
    }

    /**
     * @covers \MultiFlexi\ConfigFields::getFields
     */
    public function testGetFields(): void
    {
        $field1 = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $field2 = new ConfigField('FIELD2', 'string', 'Field 2', 'Description 2');

        $this->object->addField($field1);
        $this->object->addField($field2);

        $fields = $this->object->getFields();

        $this->assertCount(2, $fields);
        $this->assertArrayHasKey('FIELD1', $fields);
        $this->assertArrayHasKey('FIELD2', $fields);
    }

    /**
     * @covers \MultiFlexi\ConfigFields::addField
     */
    public function testAddField(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $fields = $this->object->getFields();
        $this->assertArrayHasKey('FIELD1', $fields);
        $this->assertSame($field, $fields['FIELD1']);
    }

    /**
     * @covers \MultiFlexi\ConfigFields::getField
     */
    public function testGetField(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $retrievedField = $this->object->getField('FIELD1');
        $this->assertSame($field, $retrievedField);
    }

    /**
     * @covers \MultiFlexi\ConfigFields::getFieldByCode
     */
    public function testGetFieldByCode(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $retrievedField = $this->object->getFieldByCode('FIELD1');
        $this->assertSame($field, $retrievedField);
    }

    /**
     * @covers \MultiFlexi\ConfigFields::getEnvArray
     */
    public function testGetEnvArray(): void
    {
        $field1 = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $field1->setValue('Value1');
        $field2 = new ConfigField('FIELD2', 'string', 'Field 2', 'Description 2');
        $field2->setValue('Value2');

        $this->object->addField($field1);
        $this->object->addField($field2);

        $envArray = $this->object->getEnvArray();

        $this->assertArrayHasKey('FIELD1', $envArray);
        $this->assertArrayHasKey('FIELD2', $envArray);
        $this->assertEquals('Value1', $envArray['FIELD1']);
        $this->assertEquals('Value2', $envArray['FIELD2']);
    }

    /**
     * @covers \MultiFlexi\ConfigFields::current
     */
    public function testCurrent(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertSame($field, $this->object->current());
    }

    /**
     * @covers \MultiFlexi\ConfigFields::key
     */
    public function testKey(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertEquals('FIELD1', $this->object->key());
    }

    /**
     * @covers \MultiFlexi\ConfigFields::next
     */
    public function testNext(): void
    {
        $field1 = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $field2 = new ConfigField('FIELD2', 'string', 'Field 2', 'Description 2');

        $this->object->addField($field1);
        $this->object->addField($field2);

        $this->object->next();
        $this->assertSame($field2, $this->object->current());
    }

    /**
     * @covers \MultiFlexi\ConfigFields::rewind
     */
    public function testRewind(): void
    {
        $field1 = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $field2 = new ConfigField('FIELD2', 'string', 'Field 2', 'Description 2');

        $this->object->addField($field1);
        $this->object->addField($field2);

        $this->object->next();
        $this->object->rewind();
        $this->assertSame($field1, $this->object->current());
    }

    /**
     * @covers \MultiFlexi\ConfigFields::valid
     */
    public function testValid(): void
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertTrue($this->object->valid());
        $this->object->next();
        $this->assertFalse($this->object->valid());
    }
}
