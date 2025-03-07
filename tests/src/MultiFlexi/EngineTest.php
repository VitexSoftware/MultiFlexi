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

use MultiFlexi\Engine;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:38.
 */
class EngineTest extends \PHPUnit\Framework\TestCase
{
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new Engine();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \MultiFlexi\Engine::setMyKey
     *
     * @todo   Implement testsetMyKey().
     */
    public function testsetMyKey(): void
    {
        $this->assertEquals('', $this->object->setMyKey());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Engine::saveToSQL
     *
     * @todo   Implement testsaveToSQL().
     */
    public function testsaveToSQL(): void
    {
        $this->assertEquals('', $this->object->saveToSQL());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Engine::exportEnv
     *
     * @todo   Implement testexportEnv().
     */
    public function testexportEnv(): void
    {
        $this->assertEquals('', $this->object->exportEnv());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
