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
class CustomAppConfigForm extends EngineForm
{
    private array $modulesEnv;

    /**
     * @param type $engine
     */
    public function __construct($engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'custserviceconfig.php']);
        $appId = $engine->getDataValue('app_id');

        $job = new \MultiFlexi\Job(['company_id' => $engine->getDataValue('company_id'), 'app_id' => $appId], ['autoload' => false]);
        $values = $job->getFullEnvironment();

        foreach (\MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id')) as $fieldInfo) {
            if ($fieldInfo['type'] === 'checkbox') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB5\Widgets\Toggle($fieldInfo['keyname'], \array_key_exists($fieldInfo['keyname'], $values) ? ($values[$fieldInfo['keyname']]['value'] === 'true' ? true : false) : $fieldInfo['defval'], 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldInfo['keyname'], \array_key_exists($fieldInfo['keyname'], $values) ? $values[$fieldInfo['keyname']]['value'] : $fieldInfo['defval'], ['type' => $fieldInfo['type']]);
            }

            $this->addInput($input, $fieldInfo['keyname'].'&nbsp;('.$fieldInfo['source'].')', $fieldInfo['defval'], $fieldInfo['description']);
        }

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));
        $this->addItem(new \Ease\TWB5\SubmitButton(_('Save'), 'success btn-lg btn-block'));
    }
}
