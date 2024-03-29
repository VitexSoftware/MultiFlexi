<?php

namespace MultiFlexi\Ui;

/**
 * Multi Flexi - Application Launch Form
 *
 * @package AbraFlexi\MultiFlexi
 * @copyright  2015-2023 Vitex Software
 * @license    https://opensource.org/licenses/MIT MIT
 */
class AppLaunchForm extends \Ease\TWB4\Form
{
    /**
     * Application Launch Form
     *
     * @param \MultiFlexi\Application $app
     * @param int $company
     */
    public function __construct(\MultiFlexi\Application $app, int $company)
    {
        parent::__construct(['name' => 'appLaunchForm', 'action' => 'newjob.php', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);
        $appId = $app->getMyKey();

        $job = new \MultiFlexi\Job(['company_id' => $company, 'app_id' => $appId], ['autoload' => false]);
        $env = $job->getFullEnvironment();

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $appId));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $company));

        /* check if app requires upload fields */
        $appFields = \MultiFlexi\Conffield::getAppConfigs($appId);

//        $this->addItem(new EnvironmentView($env));
        $this->addItem("<hr>");

        /* for each upload field add file input */
        foreach ($appFields as $fieldKey => $fieldProps) {
            switch ($fieldProps['type']) {
                case 'file':
                    $this->addInput(new \Ease\Html\InputFileTag($fieldKey, $fieldProps['defval'], ['id' => 'input' . $fieldProps['keyname']]), $fieldProps['keyname'], $fieldProps['defval'], $fieldProps['description']);
                    break;
                case 'email':
                    if (array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\Html\InputEmailTag($fieldKey, $fieldProps['defval']), $fieldKey, $fieldProps['defval'], $fieldProps['description']);
                    }
                    break;
                case 'checkbox':
                    if (array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\TWB4\Widgets\Toggle($fieldKey, $fieldProps['defval']), $fieldKey . '&nbsp;', $fieldProps['defval'], $fieldProps['description']);
                    }
                    break;

                default:
                    if (array_key_exists($fieldKey, $env) === false) {
                        $this->addInput(new \Ease\Html\InputTextTag($fieldKey, $fieldProps['defval']), $fieldKey, $fieldProps['defval'], $fieldProps['description']);
                    }

                    break;
            }
        }

        $this->addItem(new AppExecutorSelect($app));
        $this->addItem(new \Ease\TWB4\SubmitButton([_('Launch now') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'success btn-lg btn-block '));
    }
}
