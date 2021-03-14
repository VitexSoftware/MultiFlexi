<?php

/**
 * Multi FlexiBee Setup  - AppToCompany class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup;

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

    public function performInit() {
        $this->setEnvironment();
        $app->runInit();
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
        unset($data['interv']);
        return parent::deleteFromSQL($data);
    }

    public function setEnvironment() {
        $cmp = new Company((int) $this->getDataValue('company_id'));
        $cmp->setEnvironment();


        $envNames = [
            'EASE_MAILTO' => $this->getDataValue('email'),
            'EASE_LOGGER' => empty($this->getDataValue('email')) ? 'syslog' : 'syslog|email'
        ];
        $this->exportEnv($envNames);


        $customConfig = new Configuration();
        $customConfig->setEnvironment($cmp->getMyKey(), $app->getMyKey());


        $exec = $app->getDataValue('setup');
        $cmp->addStatusMessage('setup begin' . $exec . '@' . $cmp->getDataValue('nazev'));
        $cmp->addStatusMessage(shell_exec($exec), 'debug');
        $cmp->addStatusMessage('setu end' . $exec . '@' . $cmp->getDataValue('nazev'));
    }

}
