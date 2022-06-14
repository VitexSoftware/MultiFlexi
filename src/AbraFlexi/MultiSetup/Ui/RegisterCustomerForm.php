<?php

/**
 * Multi Flexi  - New Customer registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Registered AbraFlexi instance editor Form
 *
 * @author 
 */
class RegisterCustomerForm extends EngineForm {

    public function finalize() {
        $this->addInput(new \Ease\Html\InputTextTag('firstname'),
                _('First Name'));
        $this->addInput(new \Ease\Html\InputTextTag('lastname'),
                _('Lastname'));

        $this->addInput(new \Ease\Html\InputTextTag('login'),
                _('Login'));

        $this->addInput(new \Ease\Html\InputTextTag('password'),
                _('Password'));

        $this->addInput(new \Ease\Html\InputTextTag('email'),
                _('Email'));

        $this->addInput(new \Ease\TWB4\SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new \Ease\Html\InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }

}
