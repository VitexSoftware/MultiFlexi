<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022 Vitex Software
 */

namespace MultiFlexi\Auth;

/**
 * Description of Authenticator
 *
 * @author vitex
 */
class Authenticator extends AbstractAuthenticator {

    public function __construct($requiredScope = null) {
        parent::__construct($requiredScope);
    }

    protected function getUserByToken(string $token): array {
        $tokener = new \MultiFlexi\Token($token);
        return $tokener->getUser();
    }

}
