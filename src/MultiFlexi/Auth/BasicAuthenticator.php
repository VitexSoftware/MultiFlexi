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
 * Description of ApiKeyAuthenticator.
 *
 * @author vitex
 */
class BasicAuthenticator extends Authenticator
{
    public function __construct($requiredScope = null)
    {
        parent::__construct($requiredScope);
    }

    public function __invoke(\Psr\Http\Message\ServerRequestInterface &$request, \Dyorg\TokenAuthentication\TokenSearch $tokenSearch)
    {
        $prober = new \MultiFlexi\User($arguments['user']);

        return $prober->getUserID() && \strlen($arguments['password']) && $prober->isAccountEnabled() && $prober->passwordValidation($arguments['password'], $prober->getDataValue($prober->passwordColumn));
    }
}
