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
use Ease\TWB5\SubmitButton;

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
        $this->addInput(new InputTextTag('ic'), _('Organization ID'));
        $this->addInput(new InputEmailTag('email'), _('Send notification to'));
        $this->addInput(new CustomerSelect('customer'), _('Customer'));
        $imgInput = $this->addInput(new \Ease\Html\InputFileTag('imageraw'), _('Company Logo'));

        $this->addItem(new InputHiddenTag('enabled', '1'));
        $this->addItem(new SubmitButton(_('Save'), 'success btn-lg btn-block'));

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new InputHiddenTag('id'));
        }
    }
}
