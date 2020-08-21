<?php

/**
 * Multi FlexiBee Setup  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ImgTag;
use Ease\Html\InputEmailTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\SemaforLight;
use Ease\TWB4\Widgets\Toggle;

/**
 * Registered FlexiBee instance editor Form
 *
 * @author 
 */
class RegisterCompanyForm extends EngineForm {

    public function afterAdd() {
        $this->addInput(new InputTextTag('nazev'), _('Company name'));

        $this->addInput(new InputTextTag('company'),
                _('FlexiBee company code'));


        $this->addInput(new InputTextTag('ic'), _('Organization ID'));

        $this->addInput(new InputEmailTag('email'), _('Send notification to'));
        
        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        $this->addInput(new FlexiBeeSelect('flexibee'), _('FlexiBee server'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('rw')),
                _('write permission'));
        $this->addItem(new InputHiddenTag('rw'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('setup')),
                _('Setup performed'));
        $this->addItem(new InputHiddenTag('setup'));

        $this->addInput(new SemaforLight($this->engine->getDataValue('webhook')),
                _('WebHook established'));
        $this->addItem(new InputHiddenTag('webhook'));


        $this->addInput(new Toggle('enabled'), _('Enabled'));

        $this->addInput(new SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }

}
