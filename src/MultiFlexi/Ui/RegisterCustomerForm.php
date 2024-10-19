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

/**
 * Registered AbraFlexi instance editor Form.
 *
 * @author
 */
class RegisterCustomerForm extends EngineForm
{
    public function finalize(): void
    {
        $this->addInput(
            new \Ease\Html\InputTextTag('firstname'),
            _('First Name'),
        );
        $this->addInput(
            new \Ease\Html\InputTextTag('lastname'),
            _('Lastname'),
        );

        $this->addInput(
            new \Ease\Html\InputTextTag('login'),
            _('Login'),
        );

        $this->addInput(
            new \Ease\Html\InputPasswordTag('password'),
            _('Password'),
        );

        $this->addInput(
            new \Ease\Html\InputTextTag('email'),
            _('Email'),
        );

        $this->addInput(new \Ease\TWB4\SubmitButton(_('Save'), 'success'));

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new \Ease\Html\InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }

        parent::finalize();
    }
}
