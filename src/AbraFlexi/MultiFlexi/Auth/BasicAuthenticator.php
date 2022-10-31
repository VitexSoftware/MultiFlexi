<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Auth;

/**
 * Description of ApiKeyAuthenticator
 *
 * @author vitex
 */
class BasicAuthenticator extends Authenticator {

    public function __construct($requiredScope = null) {
        parent::__construct($requiredScope);
    }

    public function __invoke(\Psr\Http\Message\ServerRequestInterface &$request, \Dyorg\TokenAuthentication\TokenSearch $tokenSearch) {
        $prober = new AbraFlexi\MultiFlexi\User($arguments['user']);
        return $prober->getUserID() && strlen($arguments['password']) && $prober->isAccountEnabled() && $prober->passwordValidation($arguments['password'], $prober->getDataValue($prober->passwordColumn));
    }

}
