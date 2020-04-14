<?php

/**
 * Multi FlexiBee Setup  - AppToCompany class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup;

/**
 * Description of AppToCompany
 *
 * @author vitex
 */
class AppToCompany extends Engine {

    public function __construct($identifier = null, $options = []) {
        $this->myTable = 'appcompany';
        parent::__construct($identifier, $options);
    }

    public function setState(bool $state) {
        if ($state === true) {
            $result = $this->insertToSQL();
        } else {
            $result = $this->deleteFromSQL();
        }
        return $result;
    }

}
