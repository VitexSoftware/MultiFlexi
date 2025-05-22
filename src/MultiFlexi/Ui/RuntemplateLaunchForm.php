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
 * @copyright  2015-2024 Vitex Software
 * @license    https://opensource.org/licenses/MIT MIT
 */
class RuntemplateLaunchForm extends \Ease\TWB5\Form
{
    /**
     * Application Launch Form.
     */
    public function __construct(\MultiFlexi\Application $app, int $companyId)
    {
        parent::__construct(['name' => 'appLaunchForm', 'action' => 'newjob.php', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);
        $appId = $app->getMyKey();

        $runtemplater = new \MultiFlexi\RunTemplate();
        $runtemplates = $runtemplater->listingQuery()->where('company_id', $companyId)->where('app_id', $appId);

        $this->addItem(nl2br(print_r($runtemplates, true))); // TODO Choose Runtemplate

        $job = new \MultiFlexi\Job(['company_id' => $companyId, 'app_id' => $appId], ['autoload' => false]);
        $env = $job->getFullEnvironment();

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $appId));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $companyId));

        /* check if app requires upload fields */
        $appFields = \MultiFlexi\Conffield::getAppConfigs($appId);

        //        $this->addItem(new EnvironmentView($env));
        $this->addItem('<hr>');

        /* for each upload field add file input */
        foreach ($appFields as $fieldKey => $fieldProps) {
            switch ($fieldProps['type']) {
                case 'file':
                    $this->addInput(new \Ease\Html\InputFileTag($fieldKey, $fieldProps['defval'], ['id' => 'input'.$fieldProps['keyname']]), $fieldProps['keyname'], $fieldProps['defval'], $fieldProps['description']);

                    break;
                case 'email':
                    if (\array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\Html\InputEmailTag($fieldKey, $fieldProps['defval']), $fieldKey, $fieldProps['defval'], $fieldProps['description']);
                    }

                    break;
                case 'checkbox':
                    if (\array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\TWB5\Widgets\Toggle($fieldKey, $fieldProps['defval']), $fieldKey.'&nbsp;', $fieldProps['defval'], $fieldProps['description']);
                    }

                    break;

                default:
                    if (\array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\Html\InputTextTag($fieldKey, $fieldProps['defval']), $fieldKey, $fieldProps['defval'], $fieldProps['description']);
                    }

                    break;
            }
        }

        $this->addItem(new AppExecutorSelect($app));
        $this->addItem(new \Ease\TWB5\SubmitButton([_('Launch now').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'success btn-lg btn-block '));
        $this->addItem(new \Ease\TWB5\LinkButton('schedule.php?app_id='.$appId.'&company_id='.$companyId, [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg'));
    }
}
