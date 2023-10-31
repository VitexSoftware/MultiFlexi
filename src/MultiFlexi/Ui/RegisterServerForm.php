<?php

/**
 * Multi Flexi  - New AbraFlexi registration form
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Registered AbraFlexi instance editor Form
 *
 * @author
 */
class RegisterServerForm extends EngineForm
{
    public function afterAdd()
    {
        $this->addInput(new \Ease\Html\SelectTag('type', ['AbraFlexi'=>'AbraFlexi','Pohoda'=>_('Stormware Pohoda')]) , _('Server Type'));
        
        $this->addInput(
            new \Ease\Html\InputTextTag('name'),
            _('Server instance Name')
        );
        $this->addInput(
            new \Ease\Html\InputTextTag('url'),
            _('RestAPI endpoint url')
        );
        $this->addInput(
            new \Ease\Html\InputTextTag('user'),
            _('REST API Username')
        );
        $this->addInput(
            new \Ease\Html\InputPasswordTag('password'),
            _('Rest API Password')
        );

        $this->addInput(new \Ease\TWB4\SubmitButton(_('Save'), 'success'));

        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new \Ease\Html\InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }
}
