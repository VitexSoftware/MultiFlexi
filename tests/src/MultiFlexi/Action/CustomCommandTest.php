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

namespace Test\MultiFlexi\Action;

use MultiFlexi\Action\CustomCommand;

/**
 * Tests for MultiFlexi\Action\CustomCommand.
 */
class CustomCommandTest extends \PHPUnit\Framework\TestCase
{
    protected CustomCommand $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = new CustomCommand();
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::name
     */
    public function testname(): void
    {
        $this->object->setName('Test Command');
        $this->assertEquals('Test Command', $this->object->name());
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::description
     */
    public function testdescription(): void
    {
        $this->object->setDescription('This is a test command.');
        $this->assertEquals('This is a test command.', $this->object->description());
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::logo
     */
    public function testlogo(): void
    {
        $this->object->setLogo('test-logo.png');
        $this->assertEquals('test-logo.png', $this->object->logo());
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::inputs
     */
    public function testinputs(): void
    {
        $inputs = ['input1' => 'value1', 'input2' => 'value2'];
        $this->object->setInputs($inputs);
        $this->assertEquals($inputs, $this->object->inputs());
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::usableForApp
     */
    public function testusableForApp(): void
    {
        $this->object->setUsableForApp('TestApp');
        $this->assertEquals('TestApp', $this->object->usableForApp());
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::perform
     */
    public function testperform(): void
    {
        $this->object->setCommand('echo "Hello, World!"');
        $result = $this->object->perform();
        $this->assertEquals('Hello, World!', trim($result));
    }
}
