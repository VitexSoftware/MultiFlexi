<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

/**
 * Description of ConfiguredFieldBadges
 *
 * @author vitex
 */
class ConfiguredFieldBadges extends ConfigFieldsBadges {

    public function __construct($companyID, $appID) {
        $conffield = new \FlexiPeeHP\MultiSetup\Conffield();
        $appFields = $conffield->appConfigs($appID);
        $configs = new \FlexiPeeHP\MultiSetup\Configuration();
        $appConfigs = $configs->getColumnsFromSQL(['key', 'value'], ['app_id' => $appID], 'key', 'key');

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
