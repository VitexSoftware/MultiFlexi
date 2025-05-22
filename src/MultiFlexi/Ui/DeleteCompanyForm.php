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
class DeleteCompanyForm extends EngineForm
{
    public function afterAdd(): void
    {
        $this->addInput(new InputTextTag('name', null, ['disabled' => true]), _('Company name'));

        $this->addInput(
            new InputTextTag('company', null, ['disabled' => true]),
            _('AbraFlexi company code'),
        );

        $this->addInput(new InputTextTag('ic', null, ['disabled' => true]), _('Organization ID'));

        $this->addInput(new InputEmailTag('email', null, ['disabled' => true]), _('Send notification to'));

        $this->addItem(new SubmitButton('☠️☠️☠️&nbsp;'._('Confirm company Delete').'&nbsp;☠️☠️☠️', 'danger'));

        $this->addItem(new InputHiddenTag('id'));
    }
}
