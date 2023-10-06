<?php

/**
 * Multi Flexi  - AppToCompany class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of AppToCompany
 *
 * @author vitex
 */
class RunTemplate extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'runtemplate';
        parent::__construct($identifier, $options);
    }

    /**
     * Get id by App & Company
     *
     * SELECT runtemplate.id, runtemplate.interv, runtemplate.prepared, apps.nazev AS app, company.nazev AS company   FROM runtemplate LEFT JOIN apps ON runtemplate.app_id=apps.id LEFT JOIN company ON runtemplate.company_id=company.id;
     *
     * @param int $appId
     * @param int $companyId
     *
     * @return int
     */
    public function runTemplateID(int $appId, int $companyId)
    {
        $runTemplateId = intval($this->listingQuery()->where('company_id=' . $companyId . ' AND app_id=' . $appId)->select('id', true)->fetchColumn());
        return $runTemplateId ? $runTemplateId : $this->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
    }

    /**
     * Set APP State
     *
     * @param bool $state
     *
     * @return bool
     */
    public function setState(bool $state)
    {
        return $state ? $this->dbsync() : $this->deleteFromSQL();
    }

    public function performInit()
    {
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
    public function deleteFromSQL($data = null)
    {
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
    public function getAppEnvironment()
    {
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
    public function getAppInfo()
    {
        return $this->listingQuery()
                        ->select('apps.*')
                        ->select('apps.id as apps_id')
                        ->select('apps.nazev as app_name')
                        ->select('company.*')
                        ->select('abraflexis.*')
                        ->where([$this->getMyTable() . '.' . $this->getKeyColumn() => $this->getMyKey()])
                        ->leftJoin('apps ON apps.id = runtemplate.app_id')
                        ->leftJoin('company ON company.id = runtemplate.company_id')
                        ->leftJoin('abraflexis ON abraflexis.id = company.abraflexi')
                        ->fetch();
    }

    /**
     *
     */
    public function setEnvironment()
    {
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

    public function getAppsForCompany($companyID)
    {
        return $this->getColumnsFromSQL(['app_id', 'interv', 'id'], ['company_id' => $companyID], 'id', 'app_id');
    }

    /**
     * Set Provision state
     *
     * @param int|null $status 0: Unprovisioned, 1: provisioned,
     *
     * @return boolean save status
     */
    public function setProvision($status)
    {
        return $this->dbsync(['prepared' => $status]);
    }
}
