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
class RuntemplateConfigForm extends EngineForm
{
    /**
     *
     * @var array
     */
    private $modulesEnv;

    /**
     *
     * @param \MultiFlexi\RunTemplate $engine
     */
    public function __construct(\MultiFlexi\RunTemplate $engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'runtemplate.php']);
        $values = $engine->getAppEnvironment();
        foreach (\MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            if ($fieldInfo['type'] == 'checkbox') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldInfo['keyname'], array_key_exists($fieldInfo['keyname'], $values) ? ($values[$fieldInfo['keyname']]['value'] == 'true' ? true : false) : $fieldInfo['defval'], 'true', []));
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
