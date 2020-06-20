<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

/**
 * Description of CustomAppConfigForm
 *
 * @author vitex
 */
class CustomAppConfigForm extends \Ease\TWB4\Form {

    public function __construct($engine) {
        parent::__construct(['method' => 'post', 'destination' => 'custserviceconfig.php']);

        $values = $engine->getColumnsFromSQL(['key', 'value'], ['app_id' => $engine->getDataValue('app_id'), 'company_id' => $engine->getDataValue('company_id')], 'key', 'key');

        foreach (\FlexiPeeHP\MultiSetup\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            if($fieldInfo['type'] == 'checkbox'){
                $input = new \Ease\TWB4\Widgets\Toggle($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? ($values[$fieldInfo['keyname']]['value']=='true' ? true : false ): false , 'true', []);
            } else {
                $input = new \Ease\Html\InputTag($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? $values[$fieldInfo['keyname']]['value'] : '', ['type' => $fieldInfo['type']]);
            }
            
            $this->addInput($input, $fieldInfo['keyname'], null, $fieldInfo['description']);
        }
        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
    }

}
