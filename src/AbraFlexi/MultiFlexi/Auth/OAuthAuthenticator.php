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
class OAuthAuthenticator extends Authenticator {

    public function __construct($requiredScope = null) {
        parent::__construct($requiredScope);
    }

}
