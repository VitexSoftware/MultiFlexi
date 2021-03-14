<?php

/**
 * Multi FlexiBee Setup - Custom Application Config form Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of CustomAppConfigForm
 *
 * @author vitex
 */
class CustomAppConfigForm extends EngineForm {

    public function __construct($engine) {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'custserviceconfig.php']);

        $values = $engine->getColumnsFromSQL(['key', 'value'], ['app_id' => $engine->getDataValue('app_id'), 'company_id' => $engine->getDataValue('company_id')], '`key`', 'key');

        foreach (\AbraFlexi\MultiSetup\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            if ($fieldInfo['type'] == 'checkbox') {
                $input = new \Ease\TWB4\Widgets\Toggle($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? ($values[$fieldInfo['keyname']]['value'] == 'true' ? true : false ) : $fieldInfo['defval'], 'true', []);
            } else {
                $input = new \Ease\Html\InputTag($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? $values[$fieldInfo['keyname']]['value'] : $fieldInfo['defval'], ['type' => $fieldInfo['type']]);
            }

            $this->addInput($input, $fieldInfo['keyname'], $fieldInfo['defval'], $fieldInfo['description']);
        }
        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
    }

}
