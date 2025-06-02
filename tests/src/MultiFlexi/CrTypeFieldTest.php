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

namespace MultiFlexi\Tests;

use MultiFlexi\CrTypeField;
use PHPUnit\Framework\TestCase;

class CrTypeFieldTest extends TestCase
{
    public function testInitialization(): void
    {
        $crTypeField = new CrTypeField();
        $this->assertInstanceOf(CrTypeField::class, $crTypeField);
    }

    public function testSomeFunctionality(): void
    {
        $crTypeField = new CrTypeField();
        // Add assertions for CrTypeField functionality
    }
}
