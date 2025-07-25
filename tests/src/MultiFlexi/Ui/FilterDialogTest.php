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

use MultiFlexi\Ui\FilterDialog;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2023-11-07 at 13:03:15.
 *
 * @no-named-arguments
 */
class FilterDialogTest extends \PHPUnit\Framework\TestCase
{
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp(): void
    {
        $this->object = new FilterDialog();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown(): void
    {
    }

    /**
     * @covers \MultiFlexi\Ui\FilterDialog::getFilterOptions
     *
     * @todo   Implement testgetFilterOptions().
     */
    public function testgetFilterOptions(): void
    {
        $this->assertEquals('', $this->object->getFilterOptions());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Ui\FilterDialog::fixIterator
     *
     * @todo   Implement testfixIterator().
     */
    public function testfixIterator(): void
    {
        $this->assertEquals('', $this->object->fixIterator());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Ui\FilterDialog::idValueNameLabel
     *
     * @todo   Implement testidValueNameLabel().
     */
    public function testidValueNameLabel(): void
    {
        $this->assertEquals('', $this->object->idValueNameLabel());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }

    /**
     * @covers \MultiFlexi\Ui\FilterDialog::idLabelNameLabel
     *
     * @todo   Implement testidLabelNameLabel().
     */
    public function testidLabelNameLabel(): void
    {
        $this->assertEquals('', $this->object->idLabelNameLabel());
        // Remove the following lines when you implement this test.
        $this->markTestIncomplete('This test has not been implemented yet.');
    }
}
