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

namespace MultiFlexi\Auth;

/**
 * Description of Authenticator.
 *
 * @author vitex
 */
class Authenticator extends AbstractAuthenticator
{
    public function __construct($requiredScope = null)
    {
        parent::__construct($requiredScope);
    }

    protected function getUserByToken(string $token): array
    {
        $tokener = new \MultiFlexi\Token($token);

        return $tokener->getUser();
    }
}
