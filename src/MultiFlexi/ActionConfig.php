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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of ActionConfig.
 *
 * @author vitex
 */
class ActionConfig extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'actionconfig';
        parent::__construct($identifier, $options);
    }

    //        $table->addColumn('name', 'string', ['comment' => 'Module per company configurations'])
    //                ->addColumn('module', 'string', ['comment' => 'Configuration belongs to'])
    //                ->addColumn('key', 'string', ['comment' => 'Configuration Key'])
    //                ->addColumn('value', 'string', ['comment' => 'Configuration Value'])
    //                ->addColumn('mode', 'string', ['null' => true, 'length' => 10, 'default' => null, 'comment' => 'success, fail or empty'])
    //                ->addColumn('runtemplate_id', 'integer', ['null' => false])
    //                ->addColumn('DatSave', 'datetime', ['null' => true])
    //                ->addForeignKey('runtemplate_id', 'runtemplate', ['id'], ['constraint' => 'runtemplate_must_exist'])

    public function saveModeConfigs(string $mode, array $values, int $runtemplate): void
    {
        foreach ($values as $module => $configs) {
            $this->saveActionFields($module, $mode, $runtemplate, $configs);
        }
    }

    public function saveActionFields(string $module, string $mode, int $runtempate, array $configs): void
    {
        foreach ($configs as $key => $value) {
            $this->saveActionConfig($module, $key, $value, $mode, $runtempate);
        }
    }

    /**
     * @param string $module
     * @param string $key
     * @param string $value
     * @param string $mode
     * @param int    $runtempalte
     */
    public function saveActionConfig($module, $key, $value, $mode, $runtempalte): void
    {
        $cfgId = $this->listingQuery()->select(['id'], true)->where(['module' => $module, 'keyname' => $key, 'mode' => $mode, 'runtemplate_id' => $runtempalte])->fetch();

        if ($cfgId) {
            $this->updateToSQL(['value' => $value], $cfgId);
        } else {
            $this->insertToSQL(['module' => $module, 'keyname' => $key, 'mode' => $mode, 'runtemplate_id' => $runtempalte, 'value' => $value]);
        }
    }

    /**
     * @param string $module
     * @param string $key
     * @param string $value
     * @param string $mode
     * @param int    $runtempalte
     *
     * @return int
     */
    public function getModuleConfig($module, $key, $value, $mode, $runtempalte)
    {
        return $this->listingQuery()->where(['module' => $mode, 'keyname' => $key, 'mode' => $mode, 'runtemplate_id' => $runtempalte]);
    }

    /**
     * @return array
     */
    public function getRuntemplateConfig(int $runtemplateId)
    {
        return $this->listingQuery()->where('runtemplate_id', $runtemplateId);
    }
}
