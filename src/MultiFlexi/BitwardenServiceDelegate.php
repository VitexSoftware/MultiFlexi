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

namespace MultiFlexi;

/**
 * Description of BitwardenServiceDelegate.
 *
 * @author vitex
 */
class BitwardenServiceDelegate implements \Jalismrs\Bitwarden\BitwardenServiceDelegate
{
    #[\Override]
    public function getOrganizationId(): ?string
    {
    }

    #[\Override]
    public function getUserEmail(): string
    {
    }

    #[\Override]
    public function getUserPassword(): string
    {
    }

    #[\Override]
    public function restoreSession(): ?string
    {
        return '';
    }

    #[\Override]
    public function storeSession(string $session): void
    {
    }
}
