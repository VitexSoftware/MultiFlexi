<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\InputFileTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\Toggle;

/**
 * Description of RegisterAppForm
 *
 * @author vitex
 */
class RegisterAppForm extends EngineForm {

    function afterAdd() {
        $this->setTagProperty('enctype', 'multipart/form-data');
        $this->addInput(new InputTextTag('nazev'), _('Application name'));
        $this->addInput(new InputTextTag('popis'), _('Application Description'));
        $this->addInput(new InputTextTag('executable'), _('Path to binary'));
        $this->addInput(new InputTextTag('setup'), _('Command'), '', _('Command used to setup new company for use with command'));
        $this->addInput(new InputTextTag('cmdparams'), _('Command arguments'), '', _('you can use macros like {FLEXIBEE_URL} or custom defined config fields.'));

        $imgInput = $this->addInput(new InputFileTag('imageraw'), _('Application Icon'));

        $this->addInput(new Toggle('enabled', $this->engine->getDataValue('enabled') == 1), _('Enabled'));


        $this->addInput(new SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }

}
