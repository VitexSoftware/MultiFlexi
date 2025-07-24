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
 * @deprecated since version 1.20
 *
 * @author
 *
 * @no-named-arguments
 */
class RegisterServerForm extends EngineForm
{
    public function afterAdd(): void
    {
        $this->addInput(new ServerTypeSelect('type'), _('Server Type'));

        $this->addInput(
            new \Ease\Html\InputTextTag('name'),
            _('Server instance Name'),
        );
        $this->addInput(
            new \Ease\Html\InputTextTag('url'),
            _('RestAPI endpoint url'),
        );
        $this->addInput(
            new \Ease\Html\InputTextTag('user'),
            _('REST API Username'),
        );
        $this->addInput(
            new \Ease\Html\InputPasswordTag('password'),
            _('Rest API Password'),
        );

        $this->addItem(new \Ease\TWB4\SubmitButton(_('Save'), 'success'));

        if (null !== $this->engine->getDataValue('id')) {
            $this->addItem(new \Ease\Html\InputHiddenTag('id'));
        }

        if ($this->engine->getDataCount()) {
            $this->fillUp($this->engine->getData());
        }
    }
}
