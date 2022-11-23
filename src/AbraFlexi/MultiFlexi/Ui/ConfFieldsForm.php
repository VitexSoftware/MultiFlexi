<?php

/**
 * Multi Flexi  - Config Fields form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

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
        $this->addItem(new SubmitButton(_('Add'), 'success btn-block'));
    }

}
