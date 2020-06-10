<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup;

/**
 * Description of Engine
 *
 * @author vitex
 */
class Engine extends \Ease\SQL\Engine {

    public function saveToSQL($data = null, $searchForID = false) {
        if (is_null($data)) {
            $data = $this->getData();
        }
        unset($data['class']);

        if (array_key_exists('app_id', $data) && array_key_exists('company_id', $data)) {
            $found = $this->getColumnsFromSQL(['id'], ['app_id' => $data['app_id'], 'company_id' => $data['company_id']]);
            if ($found) {
                $data[$this->getKeyColumn()] = (intval($found[0]['id']));
            }
        }
        return parent::saveToSQL($data, $searchForID);
    }

    /**
     * Export given environment
     * 
     * @param array $env
     */
    public function exportEnv(array $env) {
        foreach ($env as $envName => $sqlValue) {
            $this->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $envName, $sqlValue), 'debug');
            putenv($envName . '=' . $sqlValue);
        }
    }

}
