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

    /**
     * Get id by App & Company
     * 
     * SELECT appcompany.id, appcompany.interv, appcompany.prepared, apps.nazev AS app, company.nazev AS company   FROM appcompany LEFT JOIN apps ON appcompany.app_id=apps.id LEFT JOIN company ON appcompany.company_id=company.id;
     * 
     * @param int $appId
     * @param int $companyId
     * 
     * @return int
     */
    public function appCompanyID(int $appId, int $companyId) {
        return intval($this->listingQuery()->where('company_id=' . $companyId . ' AND app_id=' . $appId)->select('id', true)->fetchColumn());
    }

    /**
     * Set APP State
     * 
     * @param bool $state
     * 
     * @return bool
     */
    public function setState(bool $state) {
        return $state ? $this->dbsync() : $this->deleteFromSQL();
    }

    public function performInit() {
        $app = new Application((int) $this->getDataValue('app_id'));
//        $this->setEnvironment();
        if (empty($app->getDataValue('setup')) == false) {
            $this->setDataValue('prepared', 0);
            $this->dbsync();
        }
//        $app->runInit();
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
            'EASE_LOGGER' => 'syslog|console',
        ];

        $appConfig = [];
        foreach ($customConfig->getAppConfig($connectionData['company_id'], $connectionData['app_id']) as $cfg) {
            $appConfig[$cfg['name']] = $cfg['value'];
        }

        $companyEnv = new CompanyEnv($connectionData['company_id']);
        return array_merge($conConfig, $companyEnv->getData(), $appConfig);
    }

    /**
     * 
     * @return array
     */
    public function getAppInfo() {
        return $this->listingQuery()
                        ->select('apps.*')
                        ->select('apps.id as apps_id')
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
            'EASE_LOGGER' => $this->getDataValue('email') ? 'console|email' : 'console'
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

    /**
     * Set Provision state
     * 
     * @param int|null $status 0: Unprovisioned, 1: provisioned, 
     * 
     * @return boolean save status
     */
    public function setProvision($status) {
        return $this->dbsync(['prepared' => $status]);
    }

}
