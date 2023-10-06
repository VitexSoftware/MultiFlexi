<?php

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of EnvsForm
 *
 * @author vitex
 */
class EnvsForm extends \Ease\TWB4\Form
{
    public function __construct($environment = [], $formProperties = [], $formContents = null)
    {
        parent::__construct($formProperties, $formProperties, $formContents);
        foreach ($environment as $key => $value) {
            $this->addInput(new \Ease\Html\InputTextTag('env[' . $key . ']', $value), $key, $value, $key);
        }
        $this->addItem(new \Ease\Html\HrTag());
        $this->addInput(new \Ease\Html\InputTextTag('env[newkey]', ''), _('New Configuration Key'));
        $this->addInput(new \Ease\Html\InputTextTag('env[newvalue]', ''), _('New Configuration value'));
        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save / Add'), 'success'));
    }
}
