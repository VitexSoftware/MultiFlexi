<?php

/**
 * Multi FlexiBee Setup  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\SemaforLight;

/**
 * Registered FlexiBee instance editor Form
 *
 * @author 
 */
class RegisterCompanyForm extends ColumnsForm {

    public function afterAdd() {
        $this->addInput(new InputTextTag('nazev'), _('Company name'));

        $this->addInput(new InputTextTag('company'),
                _('FlexiBee company code'));


        $this->addInput(new InputTextTag('ic'), _('Organization ID'));

        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        $this->addInput(new FlexiBeeSelect('flexibee'), _('FlexiBee server'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('rw')),
                _('write permission'));
        $this->addItem(new InputHiddenTag('rw'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('labels')),
                _('Labels deployed'));
        $this->addItem(new InputHiddenTag('labels'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('webhook')),
                _('WebHook established'));
        $this->addItem(new InputHiddenTag('webhook'));


        $this->addInput(new \Ease\TWB4\Widgets\Toggle('enabled'), _('Enabled'));

        $this->addInput(new SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }

}
