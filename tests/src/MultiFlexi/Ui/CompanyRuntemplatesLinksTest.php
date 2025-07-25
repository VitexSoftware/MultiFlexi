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

namespace Test\MultiFlexi\Ui;

use MultiFlexi\Ui\CompanyRuntemplatesLinks;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2024-11-07 at 12:16:46.
 *
 * @no-named-arguments
 */
class CompanyRuntemplatesLinksTest extends \PHPUnit\Framework\TestCase
{
    protected CompanyRuntemplatesLinks $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new CompanyRuntemplatesLinks();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \MultiFlexi\Ui\CompanyRuntemplatesLinks::count
     *
     * @todo   Implement testcount().
     */
    public function testcount(): void
    {
        $this->assertEquals('', $this->object->count());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
