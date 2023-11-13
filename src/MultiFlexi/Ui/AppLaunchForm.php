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
     * @param int $app
     * @param int $company
     */
    public function __construct(int $app, int $company)
    {
        parent::__construct(['name' => 'appLaunchForm', 'action' => 'newjob.php', 'method' => 'post', 'enctype' => 'multipart/form-data', 'class' => 'form-horizontal']);

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $app));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $company));
        $this->addItem(new \Ease\TWB4\SubmitButton([_('Launch now') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'success btn-lg btn-block '));

        /* check if app requires upload fields */
        $appFields = \MultiFlexi\Conffield::getAppConfigs($app);

        /* if any of fields is upload type then add file input button */
        $uploadFields = array_filter($appFields, function ($field) {
            return $field['type'] == 'file';
        });

        /* for each upload field add file input */
        foreach ($uploadFields as $field) {
            $this->addInput(new \Ease\Html\InputFileTag($field['keyname'], $field['defval'], ['id' => 'input' . $field['keyname']]), $field['keyname'], $field['defval'], $field['description']);
        }
    }
}
