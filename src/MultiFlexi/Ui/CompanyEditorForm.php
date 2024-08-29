<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

use Ease\Html\InputEmailTag;
use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;
use Ease\TWB4\Widgets\SemaforLight;
use Ease\TWB4\Widgets\Toggle;

/**
 * Registered AbraFlexi instance editor Form.
 *
 * @author
 */
class CompanyEditorForm extends EngineForm
{
    public function afterAdd(): void
    {
        $this->setTagProperty('enctype', 'multipart/form-data');
        $this->addInput(new InputTextTag('name'), _('Company name'));
        $this->addInput(new InputTextTag('code', null, ['maxlength' => 10, 'onkeyup' => 'this.value = this.value.toUpperCase();']), _('Company code'));
        $this->addInput(new InputTextTag('company'), _('Company selector'), _('firma_s_r_o_ or 30000'), _('For AbraFlexi use lowercase and for Pohoda use mServer port number'));
        $this->addInput(new InputTextTag('ic'), _('Organization ID'));
        $this->addInput(new InputEmailTag('email'), _('Send notification to'));
        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        $this->addInput(new ServerSelect('server'), _('Choose server'));
        $imgInput = $this->addInput(new \Ease\Html\InputFileTag('imageraw'), _('Company Logo'));

        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('rw')),
            _('write permission'),
        );
        $this->addItem(new InputHiddenTag('rw', false));
        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('setup')),
            _('Setup performed'),
        );
        $this->addItem(new InputHiddenTag('setup'), false);
        $this->addInput(
            new SemaforLight((bool) $this->engine->getDataValue('webhook')),
            _('WebHook established'),
        );
        $this->addItem(new InputHiddenTag('webhook'));
        $this->addInput(new Toggle('enabled'), _('Enabled'));
        $this->addInput(new SubmitButton(_('Save'), 'success'));

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new InputHiddenTag('id'));
        }
    }
}
