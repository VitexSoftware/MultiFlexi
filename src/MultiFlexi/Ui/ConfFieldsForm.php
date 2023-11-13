<?php

/**
 * Multi Flexi  - Config Fields form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2022-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\Form;
use Ease\TWB4\SubmitButton;

/**
 * Description of ConfFieldsForm
 *
 * @author vitex
 */
class ConfFieldsForm extends Form
{
    /**
     *
     * @param array $conffields
     * @param mixed $formContents
     * @param array $tagProperties
     */
    public function __construct($conffields, $formContents, $tagProperties = array())
    {
        parent::__construct(['method' => 'post', 'action' => 'conffield.php'], $tagProperties, $formContents);
        $this->addInput(new ConfigFields('type'), _('New config field type'));
        $this->addInput(new InputTextTag('keyname', array_key_exists('keyname', $conffields) ? $conffields['keyname'] : ''), _('New config field Keyword'));
        $this->addInput(new InputTextTag('defval', array_key_exists('defval', $conffields) ? $conffields['defval'] : ''), _('Default value'));
        $this->addInput(new InputTextTag('description', array_key_exists('description', $conffields) ? $conffields['description'] : ''), _('New config field description'));
        $this->addInput(new \Ease\TWB4\Widgets\Toggle('required'), _('Required'));
        if (array_key_exists('id', $conffields)) {
            $this->addItem(new InputHiddenTag('id', $conffields['id']));
            $this->addItem(new SubmitButton(_('Update'), 'success btn-block'));
        } else {
            $this->addItem(new SubmitButton(_('Add'), 'success btn-block'));
        }
    }
}
