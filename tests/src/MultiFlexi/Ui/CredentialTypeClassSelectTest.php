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

namespace Tests\MultiFlexi\Ui;

use MultiFlexi\Ui\CredentialPrototypeSelect;
use PHPUnit\Framework\TestCase;

/**
 * Test for CredentialPrototypeSelect UI component.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @covers \MultiFlexi\Ui\CredentialPrototypeSelect
 */
final class CredentialTypeClassSelectTest extends TestCase
{
    /**
     * Test basic object creation.
     */
    public function testConstruct(): void
    {
        $CredentialPrototypeSelect = new CredentialPrototypeSelect('test_field');

        $this->assertInstanceOf(CredentialPrototypeSelect::class, $CredentialPrototypeSelect);
        $this->assertInstanceOf(\Ease\Html\SelectTag::class, $CredentialPrototypeSelect);
    }

    /**
     * Test loadItems method returns array with both PHP and JSON types.
     */
    public function testLoadItemsReturnsArray(): void
    {
        $CredentialPrototypeSelect = new CredentialPrototypeSelect('test_field');

        $items = $CredentialPrototypeSelect->loadItems();

        $this->assertIsArray($items, 'loadItems should return array');
        $this->assertArrayHasKey('', $items, 'Should have empty option');
        $this->assertEquals('No CredentialType helper used', $items[''], 'Empty option should be correct');
    }

    /**
     * Test credentialTypeClasses property is array.
     */
    public function testCredentialTypeClassesProperty(): void
    {
        $CredentialPrototypeSelect = new CredentialPrototypeSelect('test_field');

        $this->assertIsArray($CredentialPrototypeSelect->credentialTypeClasses);
    }

    /**
     * Test that loadItems includes both PHP and JSON credential types.
     */
    public function testLoadItemsIncludesBothTypes(): void
    {
        $CredentialPrototypeSelect = new CredentialPrototypeSelect('test_field');
        $items = $CredentialPrototypeSelect->loadItems();

        // Check for PHP types (should contain "(PHP)" suffix)
        $hasPHPTypes = false;

        foreach ($items as $key => $value) {
            if (str_contains($value, '(PHP)')) {
                $hasPHPTypes = true;

                break;
            }
        }

        $this->assertTrue($hasPHPTypes, 'Should include PHP credential types with (PHP) suffix');
    }
}
