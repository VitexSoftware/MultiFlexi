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

use Ease\Html\InputHiddenTag;
use Ease\Html\InputTextTag;
use Ease\TWB4\SubmitButton;

/**
 * Form for editing all ConfigField properties.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class ConfFieldsForm extends SecureForm
{
    /**
     * Specify Fields for Application.
     *
     * @param array $conffields
     * @param mixed $formContents
     * @param array $tagProperties
     */
    public function __construct($conffields, $formContents, $tagProperties = [])
    {
        parent::__construct(['method' => 'post', 'action' => 'conffield.php'], $formContents, $tagProperties);

        $this->addInput(
            new CfgFieldTypeSelect('type', \array_key_exists('type', $conffields) ? $conffields['type'] : ''),
            _('Config field type'),
        );
        $this->addInput(
            new InputTextTag('keyname', \array_key_exists('keyname', $conffields) ? $conffields['keyname'] : ''),
            _('Config field Keyword'),
        );
        $this->addInput(
            new InputTextTag('name', \array_key_exists('name', $conffields) ? $conffields['name'] : ''),
            _('Display name'),
        );
        $this->addInput(
            new InputTextTag('description', \array_key_exists('description', $conffields) ? $conffields['description'] : ''),
            _('Description'),
        );
        $this->addInput(
            new InputTextTag('hint', \array_key_exists('hint', $conffields) ? $conffields['hint'] : ''),
            _('Hint'),
        );
        $this->addInput(
            new InputTextTag('defval', \array_key_exists('defval', $conffields) ? $conffields['defval'] : ''),
            _('Default value'),
        );
        $this->addInput(
            new InputTextTag('note', \array_key_exists('note', $conffields) ? $conffields['note'] : ''),
            _('Note'),
        );

        $requiredToggle = new \Ease\TWB4\Widgets\Toggle('required');

        if (!empty($conffields['required'])) {
            $requiredToggle->setTagProperties(['checked' => 'checked']);
        }

        $this->addInput($requiredToggle, _('Required'));

        $secretToggle = new \Ease\TWB4\Widgets\Toggle('secret');

        if (!empty($conffields['secret'])) {
            $secretToggle->setTagProperties(['checked' => 'checked']);
        }

        $this->addInput($secretToggle, _('Secret'));

        $multilineToggle = new \Ease\TWB4\Widgets\Toggle('multiline');

        if (!empty($conffields['multiline'])) {
            $multilineToggle->setTagProperties(['checked' => 'checked']);
        }

        $this->addInput($multilineToggle, _('Multiline'));

        $expiringToggle = new \Ease\TWB4\Widgets\Toggle('expiring');

        if (!empty($conffields['expiring'])) {
            $expiringToggle->setTagProperties(['checked' => 'checked']);
        }

        $this->addInput($expiringToggle, _('Expiring'));

        if (\array_key_exists('id', $conffields)) {
            $this->addItem(new InputHiddenTag('id', $conffields['id']));
            $this->addItem(new SubmitButton(_('Update'), 'success btn-block'));
        } else {
            $this->addItem(new SubmitButton(_('Add'), 'success btn-block'));
        }
    }
}
