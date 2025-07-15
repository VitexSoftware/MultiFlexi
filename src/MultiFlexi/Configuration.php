<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi;

/**
 * Description of Configuration.
 *
 * @author vitex
 */
class Configuration extends \Ease\SQL\Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'configuration';
        parent::__construct($identifier, $options);
    }

    /**
     * Save data array to SQL. If $searchForID is false, update if keyColumn is set.
     *
     * @param array $data        Associative array of data
     * @param bool  $searchForID Determine whether to update or insert
     *
     * @return null|int Record ID or null on failure
     */
    #[\Override]
    public function saveToSQL($data = null, $searchForID = false)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $result = 0;
        unset($data['id'], $data['class'], $data['app_id'], $data['company_id'], $data['runtemplate_id']);

        $condition = [];

        if ($this->getDataValue('app_id')) {
            $condition['app_id'] = $this->getDataValue('app_id');
        }

        if ($this->getDataValue('company_id')) {
            $condition['company_id'] = $this->getDataValue('company_id');
        }

        if ($this->getDataValue('runtemplate_id')) {
            $condition['runtemplate_id'] = $this->getDataValue('runtemplate_id');
        }

        $this->deleteFromSQL($condition);

        foreach ($data as $column => $value) {
            $result += $this->insertToSQL(['app_id' => $this->getDataValue('app_id'), 'company_id' => $this->getDataValue('company_id'), 'runtemplate_id' => $this->getDataValue('runtemplate_id'), 'name' => $column, 'value' => $value]);
        }

        return $result;
    }

    /**
     * Převezme data do aktuálního pole dat.
     *
     * @param array $data asociativní pole dat
     */
    public function takeData(array $data): int
    {
        $cfgs = new Conffield();

        foreach ($cfgs->appConfigs($this->getDataValue('app_id')) as $cfg) {
            if ($cfg['type'] === 'checkbox') {
                $data[$cfg['keyname']] = \array_key_exists($cfg['keyname'], $data) ? 'true' : 'false';
            }
        }

        return parent::takeData($data);
    }

    /**
     * Apply Configuration.
     *
     * @param int $companyId
     * @param int $appId
     */
    public function setEnvironment($companyId, $appId): void
    {
        foreach ($this->getAppConfig($companyId, $appId) as $cfgRaw) {
            $this->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $cfgRaw['name'], $cfgRaw['value']), 'debug');
            putenv($cfgRaw['name'].'='.$cfgRaw['value']);
        }
    }

    /**
     * App Configuration values.
     */
    public function getAppConfig(int $companyId, int $appId): array
    {
        $appConfig = [];
        $appConfigRaw = $this->getColumnsFromSQL(['name', 'value'], ['company_id' => $companyId, 'app_id' => $appId]);

        foreach ($appConfigRaw as $cfg) {
            $appConfig[$cfg['name']] = $cfg['value'];
        }

        return $appConfig;
    }
}
