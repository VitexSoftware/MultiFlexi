<?php

namespace Test\MultiFlexi;

use MultiFlexi\ConfigFields;
use MultiFlexi\ConfigField;

/**
 * Tests for MultiFlexi\ConfigFields.
 */
class ConfigFieldsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigFields
     */
    protected $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new ConfigFields();
    }

    /**
     * @covers MultiFlexi\ConfigFields::getFields
     */
    public function testGetFields()
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
     * @covers MultiFlexi\ConfigFields::addField
     */
    public function testAddField()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $fields = $this->object->getFields();
        $this->assertArrayHasKey('FIELD1', $fields);
        $this->assertSame($field, $fields['FIELD1']);
    }

    /**
     * @covers MultiFlexi\ConfigFields::getField
     */
    public function testGetField()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $retrievedField = $this->object->getField('FIELD1');
        $this->assertSame($field, $retrievedField);
    }

    /**
     * @covers MultiFlexi\ConfigFields::getFieldByCode
     */
    public function testGetFieldByCode()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $retrievedField = $this->object->getFieldByCode('FIELD1');
        $this->assertSame($field, $retrievedField);
    }

    /**
     * @covers MultiFlexi\ConfigFields::getEnvArray
     */
    public function testGetEnvArray()
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
     * @covers MultiFlexi\ConfigFields::current
     */
    public function testCurrent()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertSame($field, $this->object->current());
    }

    /**
     * @covers MultiFlexi\ConfigFields::key
     */
    public function testKey()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertEquals('FIELD1', $this->object->key());
    }

    /**
     * @covers MultiFlexi\ConfigFields::next
     */
    public function testNext()
    {
        $field1 = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $field2 = new ConfigField('FIELD2', 'string', 'Field 2', 'Description 2');

        $this->object->addField($field1);
        $this->object->addField($field2);

        $this->object->next();
        $this->assertSame($field2, $this->object->current());
    }

    /**
     * @covers MultiFlexi\ConfigFields::rewind
     */
    public function testRewind()
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
     * @covers MultiFlexi\ConfigFields::valid
     */
    public function testValid()
    {
        $field = new ConfigField('FIELD1', 'string', 'Field 1', 'Description 1');
        $this->object->addField($field);

        $this->assertTrue($this->object->valid());
        $this->object->next();
        $this->assertFalse($this->object->valid());
    }
}
