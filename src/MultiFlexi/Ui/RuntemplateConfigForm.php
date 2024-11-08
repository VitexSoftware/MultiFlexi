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

        $appFields = \MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id'));

        $columns = array_keys(array_merge($appFields, $customized));

        asort($columns);

        foreach ($columns as $fieldName) {
            if (\array_key_exists($fieldName, $appFields) && \array_key_exists($fieldName, $defaults)) {
                $fieldInfo = array_merge($defaults[$fieldName], $appFields[$fieldName]);
            } else {
                $fieldInfo = array_key_exists($fieldName, $appFields) ? $appFields[$fieldName] : ['type'=>'text','source'=>_('Custom')];
            }

            $value = \array_key_exists('value', $fieldInfo) ? $fieldInfo['value'] : $fieldInfo['defval'];

            if ($fieldInfo['type'] === 'checkbox') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, $value === 'true' ? true : false, 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $value, ['type' => $fieldInfo['type']]);
            }

            $formGroup = $this->addInput($input, $fieldName.'&nbsp;('.$fieldInfo['source'].')', \array_key_exists('defval', $fieldInfo) ? $fieldInfo['defval'] : '', \array_key_exists('description', $fieldInfo) ? $fieldInfo['description'] : '');

            if (\array_key_exists('required', $fieldInfo) && $fieldInfo['required'] === true) {
                $formGroup->addTagClass('bg-primary');
            }
        }

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));

        $saveRow = new \Ease\TWB4\Row();
        $saveRow->addColumn(8, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
        $saveRow->addColumn(4, new \Ease\TWB4\LinkButton('actions.php?id='.$engine->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));
        $this->addItem($saveRow);
    }
}
