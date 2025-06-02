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

use MultiFlexi\platformServer;
use PHPUnit\Framework\TestCase;

class platformServerTest extends TestCase
{
    public function testInitialization(): void
    {
        $platformServer = new platformServer();
        $this->assertInstanceOf(platformServer::class, $platformServer);
    }

    public function testSomeFunctionality(): void
    {
        $platformServer = new platformServer();
        // Add assertions for platformServer functionality
    }
}
