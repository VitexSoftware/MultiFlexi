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
 * Generated by PHPUnit_SkeletonGenerator on 2024-11-07 at 12:15:57.
 */
class CustomCommandTest extends \PHPUnit\Framework\TestCase
{
    protected CustomCommand $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new CustomCommand();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::name
     *
     * @todo   Implement testname().
     */
    public function testname(): void
    {
        $this->assertEquals('', $this->object->name());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::description
     *
     * @todo   Implement testdescription().
     */
    public function testdescription(): void
    {
        $this->assertEquals('', $this->object->description());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::logo
     *
     * @todo   Implement testlogo().
     */
    public function testlogo(): void
    {
        $this->assertEquals('', $this->object->logo());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::inputs
     *
     * @todo   Implement testinputs().
     */
    public function testinputs(): void
    {
        $this->assertEquals('', $this->object->inputs());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::usableForApp
     *
     * @todo   Implement testusableForApp().
     */
    public function testusableForApp(): void
    {
        $this->assertEquals('', $this->object->usableForApp());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Action\CustomCommand::perform
     *
     * @todo   Implement testperform().
     */
    public function testperform(): void
    {
        $this->assertEquals('', $this->object->perform());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
