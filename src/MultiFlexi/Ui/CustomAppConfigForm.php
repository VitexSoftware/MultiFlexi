<?php

/**
 * Multi Flexi - Custom Application Config form Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CustomAppConfigForm
 *
 * @author vitex
 */
class CustomAppConfigForm extends EngineForm
{
    /**
     *
     * @var array
     */
    private $modulesEnv;

    /**
     *
     * @param type $engine
     */
    public function __construct($engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'custserviceconfig.php']);
        $appId = $engine->getDataValue('app_id');
//        //$app = new \MultiFlexi\Application($engine->getDataValue('app_id'));
//
//        $fieldsAlreadyDefined = \MultiFlexi\Conffield::getAppConfigs($appId);
//
//        $confField = new \MultiFlexi\Conffield();
//        $appFields = $confField->appConfigs($appId);
//
//        foreach ($appFields as $fieldName => $fieldProps) {
//            if (array_key_exists($fieldName, $fieldsAlreadyDefined) == false) {
//                $fieldProps['name'] = $fieldName;
//                $fieldProps['value'] = $fieldProps['defval'];
//                unset($fieldProps['defval']);
//                if ($engine->takeData($fieldProps) && !is_null($engine->saveToSQL())) {
//
//                }
//            }
//        }

        $job = new \MultiFlexi\Job(['company_id' => $engine->getDataValue('company_id'), 'app_id' => $appId], ['autoload' => false]);
        $values = $engine->getColumnsFromSQL(['name', 'value'], ['app_id' => $appId, 'company_id' => $engine->getDataValue('company_id')], 'name', 'name');

        foreach (\MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            if ($fieldInfo['type'] == 'checkbox') {
                $input = new \Ease\TWB4\Widgets\Toggle($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? ($values[$fieldInfo['keyname']]['value'] == 'true' ? true : false) : $fieldInfo['defval'], 'true', []);
            } else {
                $input = new \Ease\Html\InputTag($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? $values[$fieldInfo['keyname']]['value'] : $fieldInfo['defval'], ['type' => $fieldInfo['type']]);
            }
            $this->addInput($input, $fieldInfo['keyname'] . '&nbsp;(' . $fieldInfo['source'] . ')', $fieldInfo['defval'], $fieldInfo['description']);
        }
        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
    }
}
