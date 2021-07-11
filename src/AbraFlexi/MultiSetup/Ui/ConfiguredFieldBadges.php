<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of ConfiguredFieldBadges
 *
 * @author vitex
 */
class ConfiguredFieldBadges extends ConfigFieldsBadges {

    /**
     * 
     * @param int $companyID
     * @param int $appID
     */
    public function __construct(int $companyID, int $appID) {
        $conffield = new \AbraFlexi\MultiSetup\Conffield();
        $appFields = $conffield->appConfigs($appID);
        $configs = new \AbraFlexi\MultiSetup\Configuration();
        $appConfigs = $configs->getColumnsFromSQL(['name', 'value'], ['app_id' => $appID]);

        if (!empty($appFields)) {
            foreach ($appFields as $key => $fieldInfo) {
                $appFields[$key]['state'] = array_key_exists($key, $appConfigs) ? 'success' : 'warning';
            }
        }

        parent::__construct($appFields);

        if (!empty($appFields)) {
            $this->addItem(new \Ease\TWB4\LinkButton('custserviceconfig.php?app_id=' . $appID . '&amp;company_id=' . $companyID, _('Configure'), 'success btn-sm'));
        }
    }

}
