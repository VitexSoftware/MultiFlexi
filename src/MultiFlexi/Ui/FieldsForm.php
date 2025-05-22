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
 * Description of FieldsForm.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class FieldsForm extends \Ease\Container
{
    public function __construct(\MultiFlexi\ConfigFields $fields, string $prefix = '')
    {
        parent::__construct();

        foreach ($fields->getFields() as $field) {
            $this->addInput($field, $prefix);
        }
    }

    /**
     * Insert Input widget.
     */
    public function addInput(\MultiFlexi\ConfigField $field, string $prefix = ''): void
    {
        switch ($field->getType()) {
            case '':
                break;
            case 'string':
            default:
                $this->addItem(
                    new \Ease\TWB5\FormGroup(
                        $field->getName(),
                        new \Ease\Html\InputTextTag($prefix ? $prefix.'['.$field->getCode().']' : $field->getCode(), $field->getValue()),
                        $field->getHint(),
                    ),
                );

                break;
        }
    }
}
