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

use MultiFlexi\Ui\CredentialTypeSelect;
use PHPUnit\Framework\TestCase;

/**
 * Test for CredentialTypeSelect UI component.
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @covers \MultiFlexi\Ui\CredentialTypeSelect
 */
final class CredentialTypeSelectTest extends TestCase
{
    /**
     * Test basic object creation.
     */
    public function testConstruct(): void
    {
        $credentialTypeSelect = new CredentialTypeSelect('test_field', 1, null, []);

        $this->assertInstanceOf(CredentialTypeSelect::class, $credentialTypeSelect);
        $this->assertInstanceOf(\Ease\Html\SelectTag::class, $credentialTypeSelect);
    }

    /**
     * Test that it has selectize trait.
     */
    public function testSelectizeTrait(): void
    {
        $credentialTypeSelect = new CredentialTypeSelect('test_field', 1);

        $this->assertTrue(
            method_exists($credentialTypeSelect, 'selectize'),
            'CredentialTypeSelect should have selectize method from trait',
        );
    }

    /**
     * Test loadItems method returns array.
     */
    public function testLoadItemsReturnsArray(): void
    {
        $credentialTypeSelect = new CredentialTypeSelect('test_field', 1);

        $items = $credentialTypeSelect->loadItems();

        $this->assertIsArray($items, 'loadItems should return array');
        $this->assertArrayHasKey('', $items, 'Should have empty option for "Do not use"');
        $this->assertEquals('Do not use', $items[''], 'Empty option should be "Do not use"');
    }

    /**
     * Test company_id property is set correctly.
     */
    public function testCompanyIdProperty(): void
    {
        $companyId = 42;
        $credentialTypeSelect = new CredentialTypeSelect('test_field', $companyId);

        // Use reflection to access private property
        $reflection = new \ReflectionClass($credentialTypeSelect);
        $property = $reflection->getProperty('company_id');
        $property->setAccessible(true);

        $this->assertEquals($companyId, $property->getValue($credentialTypeSelect));
    }
}
