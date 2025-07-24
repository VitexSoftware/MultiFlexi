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

namespace MultiFlexi\CredentialType;

/**
 * Description of Common.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Common extends \MultiFlexi\CredentialProtoType implements \MultiFlexi\credentialTypeInterface
{
    // put your code here
    #[\Override]
    public function prepareConfigForm(): void
    {
    }

    #[\Override]
    public static function description(): string
    {
        return _('Non specialised credential type');
    }

    #[\Override]
    public static function logo(): string
    {
        return 'CommonCredentialType.svg';
    }

    #[\Override]
    public static function name(): string
    {
        return _('Common type');
    }
}
