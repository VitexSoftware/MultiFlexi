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

namespace MultiFlexi\Api\Auth;

/**
 * Description of ApiKeyAuthenticator.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class BasicAuthenticator extends Authenticator
{
    public function __construct($requiredScope = null)
    {
        parent::__construct($requiredScope);
    }

    public function __invoke(\Psr\Http\Message\ServerRequestInterface &$request, \Dyorg\TokenAuthentication\TokenSearch $tokenSearch)
    {
        $prober = \Ease\Shared::user(null, '\MultiFlexi\User');

        if ($prober->isLogged()) {
            return true;
        }

        $userInfo = $request->getUri()->getUserInfo();

        if (strstr($userInfo, ':')) {
            [$login,$password] = explode(':', $userInfo);
        } else {
            $login = $userInfo;
            $password = '';
        }

        $prober = new \MultiFlexi\User();
        $prober->loadFromSQL([$prober->loginColumn => $login]);

        return $prober->getUserID() && \strlen($password) && $prober->isAccountEnabled() && $prober->passwordValidation($password, $prober->getDataValue($prober->passwordColumn));
    }
}
