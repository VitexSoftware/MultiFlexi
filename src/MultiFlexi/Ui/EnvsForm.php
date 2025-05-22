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
 * Description of EnvsForm.
 *
 * @author vitex
 */
class EnvsForm extends \Ease\TWB5\Form
{
    public function __construct($environment = [], $formProperties = [], $formContents = null)
    {
        parent::__construct($formProperties, $formProperties, $formContents);

        foreach ($environment as $key => $value) {
            $this->addInput(new \Ease\Html\InputTextTag('env['.$key.']', $value), $key, $value, $key);
        }

        $this->addItem(new \Ease\Html\HrTag());
        $this->addInput(new \Ease\Html\InputTextTag('env[newkey]', ''), _('New Configuration Key'));
        $this->addInput(new \Ease\Html\InputTextTag('env[newvalue]', ''), _('New Configuration value'));
        $this->addItem(new \Ease\TWB5\SubmitButton(_('Save / Add'), 'success'));
    }
}
