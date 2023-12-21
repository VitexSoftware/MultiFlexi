<?php

/**
 * Multi Flexi  - AppToCompany class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

/**
 *
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
     * SELECT runtemplate.id, runtemplate.interv, runtemplate.prepared, apps.name AS app, company.name AS company   FROM runtemplate LEFT JOIN apps ON runtemplate.app_id=apps.id LEFT JOIN company ON runtemplate.company_id=company.id;
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

    public function getCompanyEnvironment()
    {
        $connectionData = $this->getAppInfo();
        $platformHelperClass = '\\MultiFlexi\\' . $connectionData['type'] . '\\Company';
        $platformHelper = new $platformHelperClass($connectionData['company_id'], $connectionData);
        return $platformHelper->getEnvironment();
    }

    /**
     *
     * @param type $companyId
     *
     * @return type
     */
    public function getCompanyTemplates($companyId)
    {
        return $this->listingQuery()->where('company_id', $companyId);
    }

    public function getCompanyAppsByInterval($companyId)
    {
        $companyApps = [
            'i' => [],
            'h' => [],
            'd' => [],
            'w' => [],
            'm' => [],
            'y' => []
        ];
        foreach ($this->getCompanyTemplates($companyId)->fetchAll() as $template) {
            $companyApps[$template['interv']][$template['app_id']] = $template;
        }
        return $companyApps;
    }

    /**
     *
     *
     *
     * @return array
     */
    public function getAppEnvironment()
    {
        $appInfo = $this->getAppInfo();
        $jobber = new Job();
        $jobber->company = new Company(intval($appInfo['company_id']));
        $jobber->application = new Application(intval($appInfo['app_id']));
        return $jobber->getFullEnvironment();
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
                        ->select('apps.name as app_name')
                        ->select('company.*')
                        ->select('servers.*')
                        ->where([$this->getMyTable() . '.' . $this->getKeyColumn() => $this->getMyKey()])
                        ->leftJoin('apps ON apps.id = runtemplate.app_id')
                        ->leftJoin('company ON company.id = runtemplate.company_id')
                        ->leftJoin('servers ON servers.id = company.server')
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
        $cmp->addStatusMessage('setup begin' . $exec . '@' . $cmp->getDataValue('name'));
        $cmp->addStatusMessage(shell_exec($exec), 'debug');
        $cmp->addStatusMessage('setu end' . $exec . '@' . $cmp->getDataValue('name'));
    }

    /**
     * All RunTemplates for GivenCompany
     *
     * @param int $companyID
     *
     * @return array
     */
    public function getPeriodAppsForCompany($companyID)
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

    public function assignAppsToCompany(int $companyId, array $appIds, string $interval)
    {
        $availbleApps = (new \MultiFlexi\CompanyApp(new Company($companyId)))->getAssigned()->fetchAll('app_id');
        $currentApps = $this->getCompanyTemplates($companyId)->where('interv', $interval)->fetchAll('app_id');
        foreach ($appIds as $appId) {
            if (array_key_exists($appId, $currentApps) === false) {
                $this->insertToSQL(['app_id' => $appId, 'company_id' => $companyId, 'interv' => $interval]);
            }
        }

        foreach ($currentApps as $currentApp) {
            if (array_search($appId, $appIds) === false) {
                $this->deleteFromSQL(['app_id' => $appId, 'company_id' => $companyId]);
            }
        }
    }

    /**
     * Return only key=>value pairs
     *
     * @param array $envData
     *
     * @return array
     */
    public static function stripToValues(array $envData)
    {
        $env = [];
        foreach ($envData as $key => $data) {
            $env[$key] = $data['value'];
        }
        return $env;
    }
}
