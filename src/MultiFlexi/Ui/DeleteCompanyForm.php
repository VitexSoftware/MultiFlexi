<?php

/**
 * Multi Flexi  - New Company registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

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
class DeleteCompanyForm extends EngineForm {

    public function afterAdd() {
        $this->addInput(new InputTextTag('nazev',null,['disabled'=>true]), _('Company name'));

        $this->addInput(new InputTextTag('company',null,['disabled'=>true]),
                _('AbraFlexi company code'));

        $this->addInput(new InputTextTag('ic',null,['disabled'=>true]), _('Organization ID'));

        $this->addInput(new InputEmailTag('email',null,['disabled'=>true]), _('Send notification to'));

        $this->addInput(new SubmitButton('☠️☠️☠️&nbsp;'. _('Confirm company Delete').'&nbsp;☠️☠️☠️', 'danger'));

        $this->addItem(new InputHiddenTag('id'));
    }

}
