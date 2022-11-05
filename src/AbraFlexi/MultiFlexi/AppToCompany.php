<?php

/**
 * Multi Flexi  - AppToCompany class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

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

    /**
     * 
     * @return array
     */
    public function getAppEnvironment() {
        $connectionData = $this->getAppInfo();
        $customConfig = new Configuration();

        $conConfig = [
            'ABRAFLEXI_URL' => $connectionData['url'],
            'ABRAFLEXI_LOGIN' => $connectionData['user'],
            'ABRAFLEXI_PASSWORD' => $connectionData['password'],
            'ABRAFLEXI_COMPANY' => $connectionData['company'],
            'EASE_EMAILTO' => $connectionData['email'],
            'EASE_LOGGER' => empty($connectionData['email']) ? 'syslog' : 'syslog|email',
        ];

        $appConfig = [];
        foreach ($customConfig->getAppConfig($connectionData['company_id'], $connectionData['app_id']) as $cfg) {
            $appConfig[$cfg['name']] = $cfg['value'];
        }

        return array_merge($conConfig, $appConfig);
    }

    /**
     * 
     * @return array
     */
    public function getAppInfo() {
        return $this->listingQuery()
                        ->select('apps.*')
                        ->select('apps.nazev as app_name')
                        ->select('company.*')
                        ->select('abraflexis.*')
                        ->where([$this->getMyTable() . '.' . $this->getKeyColumn() => $this->getMyKey()])
                        ->leftJoin('apps ON apps.id = appcompany.app_id')
                        ->leftJoin('company ON company.id = appcompany.company_id')
                        ->leftJoin('abraflexis ON abraflexis.id = company.abraflexi')
                        ->fetch();
    }

    /**
     * 
     */
    public function setEnvironment() {
        $cmp = new Company((int) $this->getDataValue('company_id'));
        $cmp->setEnvironment();

        $envNames = [
            'EASE_EMAILTO' => $this->getDataValue('email'),
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

    public function getAppsForCompany($companyID) {
        return $this->getColumnsFromSQL(['app_id', 'interv', 'id'], ['company_id' => $companyID], 'id', 'app_id');
    }
    
    
}
