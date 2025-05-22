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

/**
 * Tests for MultiFlexi\ConfigField.
 */
class ConfigFieldTest extends \PHPUnit\Framework\TestCase
{
    protected ConfigField $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new ConfigField('TEST_CODE', 'string', 'Test Name', 'Test Description', 'Test Hint', 'Test Value');
    }

    /**
     * @covers \MultiFlexi\ConfigField::setName
     */
    public function testSetName(): void
    {
        $this->object->setName('New Name');
        $this->assertEquals('New Name', $this->object->getName());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getName
     */
    public function testGetName(): void
    {
        $this->assertEquals('Test Name', $this->object->getName());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setDescription
     */
    public function testSetDescription(): void
    {
        $this->object->setDescription('New Description');
        $this->assertEquals('New Description', $this->object->getDescription());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getDescription
     */
    public function testGetDescription(): void
    {
        $this->assertEquals('Test Description', $this->object->getDescription());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setHint
     */
    public function testSetHint(): void
    {
        $this->object->setHint('New Hint');
        $this->assertEquals('New Hint', $this->object->getHint());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getHint
     */
    public function testGetHint(): void
    {
        $this->assertEquals('Test Hint', $this->object->getHint());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setValue
     */
    public function testSetValue(): void
    {
        $this->object->setValue('New Value');
        $this->assertEquals('New Value', $this->object->getValue());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getValue
     */
    public function testGetValue(): void
    {
        $this->assertEquals('Test Value', $this->object->getValue());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setCode
     */
    public function testSetCode(): void
    {
        $this->object->setCode('NEW_CODE');
        $this->assertEquals('NEW_CODE', $this->object->getCode());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getCode
     */
    public function testGetCode(): void
    {
        $this->assertEquals('TEST_CODE', $this->object->getCode());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setRequired
     */
    public function testSetRequired(): void
    {
        $this->object->setRequired(true);
        $this->assertTrue($this->object->isRequired());
    }

    /**
     * @covers \MultiFlexi\ConfigField::isRequired
     */
    public function testIsRequired(): void
    {
        $this->assertFalse($this->object->isRequired());
    }

    /**
     * @covers \MultiFlexi\ConfigField::setSecret
     */
    public function testSetSecret(): void
    {
        $this->object->setSecret(true);
        $this->assertTrue($this->object->isSecret());
    }

    /**
     * @covers \MultiFlexi\ConfigField::isSecret
     */
    public function testIsSecret(): void
    {
        $this->assertFalse($this->object->isSecret());
    }

    /**
     * @covers \MultiFlexi\ConfigField::getArray
     */
    public function testGetArray(): void
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
            'manual' => true,
        ];
        $this->assertEquals($expected, $this->object->getArray());
    }
}
