<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\InputSubmitTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\Form;

/**
 * Description of ConfFieldsForm
 *
 * @author vitex
 */
class ConfFieldsForm extends Form {

    public function __construct($conffields, $formContents, $tagProperties = array()) {
        parent::__construct(['method' => 'post','action'=>'conffiled.php'], $tagProperties, $formContents);
        $this->addInput(new ConfigFields('type'), _('New config field type'));
        $this->addInput(new InputTextTag('keyname'), _('New config field Keyword'));
        $this->addInput(new InputTextTag('description'), _('New config field description'));
        $this->addItem(new InputSubmitTag('add', _('Add')));
    }

}
