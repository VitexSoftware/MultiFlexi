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
 * Description of Csas.
 *
 * @author vitex
 */
class Csas implements \MultiFlexi\CredentialTypeHelper
{
    public static function description(): string
    {
        return _('Česká Spořitelna a.s.');
    }
}
