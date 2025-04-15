<?php

namespace Test\MultiFlexi;

use MultiFlexi\ConfigField;

/**
 * Tests for MultiFlexi\ConfigField.
 */
class ConfigFieldTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigField
     */
    protected $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new ConfigField('TEST_CODE', 'string', 'Test Name', 'Test Description', 'Test Hint', 'Test Value');
    }

    /**
     * @covers MultiFlexi\ConfigField::setName
     */
    public function testSetName()
    {
        $this->object->setName('New Name');
        $this->assertEquals('New Name', $this->object->getName());
    }

    /**
     * @covers MultiFlexi\ConfigField::getName
     */
    public function testGetName()
    {
        $this->assertEquals('Test Name', $this->object->getName());
    }

    /**
     * @covers MultiFlexi\ConfigField::setDescription
     */
    public function testSetDescription()
    {
        $this->object->setDescription('New Description');
        $this->assertEquals('New Description', $this->object->getDescription());
    }

    /**
     * @covers MultiFlexi\ConfigField::getDescription
     */
    public function testGetDescription()
    {
        $this->assertEquals('Test Description', $this->object->getDescription());
    }

    /**
     * @covers MultiFlexi\ConfigField::setHint
     */
    public function testSetHint()
    {
        $this->object->setHint('New Hint');
        $this->assertEquals('New Hint', $this->object->getHint());
    }

    /**
     * @covers MultiFlexi\ConfigField::getHint
     */
    public function testGetHint()
    {
        $this->assertEquals('Test Hint', $this->object->getHint());
    }

    /**
     * @covers MultiFlexi\ConfigField::setValue
     */
    public function testSetValue()
    {
        $this->object->setValue('New Value');
        $this->assertEquals('New Value', $this->object->getValue());
    }

    /**
     * @covers MultiFlexi\ConfigField::getValue
     */
    public function testGetValue()
    {
        $this->assertEquals('Test Value', $this->object->getValue());
    }

    /**
     * @covers MultiFlexi\ConfigField::setCode
     */
    public function testSetCode()
    {
        $this->object->setCode('NEW_CODE');
        $this->assertEquals('NEW_CODE', $this->object->getCode());
    }

    /**
     * @covers MultiFlexi\ConfigField::getCode
     */
    public function testGetCode()
    {
        $this->assertEquals('TEST_CODE', $this->object->getCode());
    }

    /**
     * @covers MultiFlexi\ConfigField::setRequired
     */
    public function testSetRequired()
    {
        $this->object->setRequired(true);
        $this->assertTrue($this->object->isRequired());
    }

    /**
     * @covers MultiFlexi\ConfigField::isRequired
     */
    public function testIsRequired()
    {
        $this->assertFalse($this->object->isRequired());
    }

    /**
     * @covers MultiFlexi\ConfigField::setSecret
     */
    public function testSetSecret()
    {
        $this->object->setSecret(true);
        $this->assertTrue($this->object->isSecret());
    }

    /**
     * @covers MultiFlexi\ConfigField::isSecret
     */
    public function testIsSecret()
    {
        $this->assertFalse($this->object->isSecret());
    }

    /**
     * @covers MultiFlexi\ConfigField::getArray
     */
    public function testGetArray()
    {
        $expected = [
            'keyname' => 'TEST_CODE',
            'name' => 'Test Name',
            'description' => 'Test Description',
            'hint' => 'Test Hint',
            'value' => 'Test Value',
            'type' => 'string',
            'defval' => null,
            'required' => false,
            'source' => '',
            'note' => '',
            'secret' => false,
            'manual' => true
        ];
        $this->assertEquals($expected, $this->object->getArray());
    }
}
