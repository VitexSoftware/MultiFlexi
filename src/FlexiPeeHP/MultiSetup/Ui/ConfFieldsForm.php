<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\Form;
use Ease\TWB4\SubmitButton;

/**
 * Description of ConfFieldsForm
 *
 * @author vitex
 */
class ConfFieldsForm extends Form {

    public function __construct($conffields, $formContents, $tagProperties = array()) {
        parent::__construct(['method' => 'post', 'action' => 'conffield.php'], $tagProperties, $formContents);
        $this->addItem(new InputHiddenTag('id', $conffields->getMyKey()));
        $this->addInput(new ConfigFields('type'), _('New config field type'));
        $this->addInput(new InputTextTag('keyname'), _('New config field Keyword'));
        $this->addInput(new InputTextTag('defval'), _('Default value'));
        $this->addInput(new InputTextTag('description'), _('New config field description'));
        $this->addItem(new SubmitButton( _('Add'),'success btn-block'));
    }

}
