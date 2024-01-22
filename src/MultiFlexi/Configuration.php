<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace MultiFlexi;

/**
 * Description of Configuration
 *
 * @author vitex
 */
class Configuration extends \Ease\SQL\Engine
{
    public $myTable = 'configuration';

    public function __construct($identifier = null, $options = array())
    {
        parent::__construct($identifier, $options);
    }

    /**
     * Uloží pole dat do SQL. Pokud je $SearchForID 0 updatuje pokud ze nastaven  keyColumn.
     *
     * @param array $data        asociativní pole dat
     * @param bool  $searchForID Zjistit zdali updatovat nebo insertovat
     *
     * @return int|null ID záznamu nebo null v případě neůspěchu
     */
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (is_null($data)) {
            $data = $this->getData();
        }
        $result = 0;
        unset($data['app_id']);
        unset($data['company_id']);
        $this->deleteFromSQL(['app_id' => $this->getDataValue('app_id'), 'company_id' => $this->getDataValue('company_id')]);
        foreach ($data as $column => $value) {
            $result += $this->insertToSQL(['app_id' => $this->getDataValue('app_id'), 'company_id' => $this->getDataValue('company_id'), 'name' => $column, 'value' => $value]);
        }
        return $result;
    }

    /**
     * Převezme data do aktuálního pole dat.
     *
     * @param array $data asociativní pole dat
     *
     * @return int
     */
    public function takeData($data)
    {
        $cfgs = new Conffield();
        foreach ($cfgs->appConfigs($this->getDataValue('app_id')) as $cfg) {
            if ($cfg['type'] == 'checkbox') {
                $data[$cfg['keyname']] = array_key_exists($cfg['keyname'], $data) ? 'true' : 'false';
            }
        }
        return parent::takeData($data);
    }

    /**
     * Apply Configuration
     *
     * @param int $companyId
     * @param int $appId
     */
    public function setEnvironment($companyId, $appId)
    {
        foreach ($this->getAppConfig($companyId, $appId) as $cfgRaw) {
            $this->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $cfgRaw['name'], $cfgRaw['value']), 'debug');
            putenv($cfgRaw['name'] . '=' . $cfgRaw['value']);
        }
    }

    /**
     * App Configuration values
     *
     * @param int $companyId
     * @param int $appId
     *
     * @return array
     */
    public function getAppConfig($companyId, $appId)
    {
        return $this->getColumnsFromSQL(['name', 'value'], ['company_id' => $companyId, 'app_id' => $appId]);
    }
}
