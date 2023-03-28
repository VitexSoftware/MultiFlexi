<?php

/**
 * Multi Flexi  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ImgTag;
use Ease\Html\InputEmailTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\SemaforLight;
use Ease\TWB4\Widgets\Toggle;

/**
 * Registered AbraFlexi instance editor Form
 *
 * @author 
 */
class RegisterCompanyForm extends EngineForm {

    public function afterAdd() {
        $this->addInput(new InputTextTag('nazev'), _('Company name'));

        $this->addInput(new InputTextTag('company'),
                _('AbraFlexi company code'));

        $this->addInput(new InputTextTag('ic'), _('Organization ID'));

        $this->addInput(new InputEmailTag('email'), _('Send notification to'));

        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        $this->addInput(new \AbraFlexiSelect('abraflexi'), _('AbraFlexi server'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('rw')),
                _('write permission'));
        $this->addItem(new InputHiddenTag('rw', false));

        $this->addInput(new SemaforLight($this->engine->getDataValue('setup')),
                _('Setup performed'));
        $this->addItem(new InputHiddenTag('setup'), false);

        $this->addInput(new SemaforLight($this->engine->getDataValue('webhook')),
                _('WebHook established'));
        $this->addItem(new InputHiddenTag('webhook'));

        $this->addInput(new Toggle('enabled'), _('Enabled'));

        $this->addInput(new SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new InputHiddenTag('id'));
        }
    }

}
