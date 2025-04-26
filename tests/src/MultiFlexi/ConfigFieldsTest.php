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
