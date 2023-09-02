<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Auth;

/**
 * Description of ApiKeyAuthenticator
 *
 * @author vitex
 */
class ApiKeyAuthenticator extends Authenticator {

    public function __construct($requiredScope = null) {
        parent::__construct($requiredScope);
    }

}
