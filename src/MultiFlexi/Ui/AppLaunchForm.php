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
 * MultiFlexi - Application Launch Form.
 *
 * @copyright  2015-2026 Vitex Software
 * @license    https://opensource.org/licenses/MIT MIT
 *
 * @no-named-arguments
 */
class AppLaunchForm extends SecureForm
{
    /**
     * Application Launch Form.
     */
    public function __construct(\MultiFlexi\Application $app, int $companyId)
    {
        parent::__construct(['name' => 'appLaunchForm', 'action' => 'newjob.php', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);
        $appId = $app->getMyKey();

        $job = new \MultiFlexi\Job(['company_id' => $companyId, 'app_id' => $appId], ['autoload' => false]);
        $env = $job->getFullEnvironment();
        $envArray = \is_array($env) ? $env : (method_exists($env, 'getFields') ? $env->getFields() : []);

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $appId));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $companyId));

        /* check if app requires upload fields */
        $appFieldsObj = \MultiFlexi\Conffield::getAppConfigs($app);
        $appFields = \is_array($appFieldsObj) ? $appFieldsObj : $appFieldsObj->getFields();

        $this->addItem('<hr>');

        /* for each upload field add file input */
        foreach ($appFields as $fieldKey => $fieldProps) {
            if ($fieldProps instanceof \MultiFlexi\ConfigField) {
                switch ($fieldProps->getType()) {
                    case 'file':
                        $this->addInput(new \Ease\Html\InputFileTag($fieldKey, $fieldProps->getDefaultValue(), ['id' => 'input'.$fieldProps->getName()]), $fieldProps->getName(), $fieldProps->getDefaultValue(), $fieldProps->getDescription());

                        break;
                    case 'email':
                        if (!\array_key_exists($fieldKey, $envArray)) {
                            $this->addInput(new \Ease\Html\InputEmailTag($fieldKey, $fieldProps->getDefaultValue()), $fieldKey, $fieldProps->getDefaultValue(), $fieldProps->getDescription());
                        }

                        break;
                    case 'checkbox':
                        if (!\array_key_exists($fieldKey, $envArray)) {
                            $this->addInput(new \Ease\TWB4\Widgets\Toggle($fieldKey, (bool) $fieldProps->getDefaultValue()), $fieldKey.'&nbsp;', $fieldProps->getDefaultValue(), $fieldProps->getDescription());
                        }

                        break;

                    default:
                        if (!\array_key_exists($fieldKey, $envArray)) {
                            $this->addInput(new \Ease\Html\InputTextTag($fieldKey, $fieldProps->getDefaultValue()), $fieldKey, $fieldProps->getDefaultValue(), $fieldProps->getDescription());
                        }

                        break;
                }
            }
        }

        $this->addItem(new AppExecutorSelect($app));
        $this->addItem(new \Ease\TWB4\SubmitButton([_('Launch now').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'success btn-lg btn-block '));
        $this->addItem(new \Ease\TWB4\LinkButton('schedule.php?app_id='.$appId.'&company_id='.$companyId, [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg'));
    }
}
