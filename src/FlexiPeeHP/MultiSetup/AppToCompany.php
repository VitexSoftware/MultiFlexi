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
        return $state ? $this->dbsync() : $this->deleteFromSQL();
    }

    /**
     * Delete record ignoring interval
     * 
     * @param array $data
     * 
     * @return int
     */
    public function deleteFromSQL($data = null) {
        if (is_null($data)) {
            $data = $this->getData();
        }
        unset($data['interval']);
        return parent::deleteFromSQL($data);
    }

}
