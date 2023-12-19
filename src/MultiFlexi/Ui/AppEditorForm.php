<?php

/**
 * Multi Flexi - Company Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

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
class AppEditorForm extends EngineForm
{
    public function afterAdd()
    {
        $this->setTagProperty('enctype', 'multipart/form-data');
        $this->addInput(new InputTextTag('name'), _('Application name'));
        $this->addInput(new InputTextTag('description'), _('Application Description'));
        $this->addInput(new InputTextTag('executable'), _('Path to binary'));
        $this->addInput(new InputTextTag('setup'), _('Setup Command'), '', _('Command used to setup new company for use with command'));
        $this->addInput(new InputTextTag('cmdparams'), _('Command arguments'), '', _('you can use macros like {ABRAFLEXI_URL} or custom defined config fields.'));
        $imgInput = $this->addInput(new InputFileTag('imageraw'), _('Application Icon'));
        $this->addInput(new Toggle('enabled', $this->engine->getDataValue('enabled') == 1), _('Enabled'));
        $this->addInput(new InputTextTag('homepage'), _('Application Homepage'));
        $this->addInput(new InputTextTag('ociimage'), _('Container image'));
        $this->addInput(new InputTextTag('version'), _('Current Version of Application'));
        $this->addInput(new InputTextTag('deploy'), _('Application deploy command'));
        $this->addInput(new SubmitButton(_('Save'), 'success'));
        if (!is_null($this->engine->getDataValue('id'))) {
            $this->addItem(new InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }
}
