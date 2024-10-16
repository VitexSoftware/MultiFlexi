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

namespace MultiFlexi\Ui;

/**
 * Description of CustomAppConfigForm.
 *
 * @author vitex
 */
class RuntemplateConfigForm extends EngineForm
{

    private array $modulesEnv;

    public function __construct(\MultiFlexi\RunTemplate $engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'runtemplate.php']);
        $defaults = $engine->getAppEnvironment();
        $customized = $engine->getRuntemplateEnvironment();

        foreach (\MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            $fieldName = $fieldInfo['keyname'];
            if (array_key_exists($fieldName, $customized)) {
                $cfg = array_merge($fieldInfo, $customized[$fieldName]);
            } else {
                if(array_key_exists($fieldName, $defaults)){
                    $cfg = array_merge($fieldInfo, $defaults[$fieldName]);
                } else {
                    $cfg = $fieldInfo;
                }
            }
            $value = array_key_exists('value', $cfg) ? $cfg['value'] : $cfg['defval'];

            if ($fieldInfo['type'] === 'checkbox') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, ($value === 'true' ? true : false), 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $value, ['type' => $fieldInfo['type']]);
            }

            
            $formGroup = $this->addInput($input, $fieldName . '&nbsp;(' . $fieldInfo['source'] . ')', $fieldInfo['defval'], $fieldInfo['description']);
            if(array_key_exists('required', $fieldInfo) && $fieldInfo['required'] == true){
                $formGroup->addTagClass('bg-primary');
            }
            
        }

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
    }
}
