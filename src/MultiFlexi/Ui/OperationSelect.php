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
 * Select widget for choosing an event operation type.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class OperationSelect extends \Ease\Html\SelectTag
{
    /**
     * @param string $name         HTML form field name
     * @param string $defaultValue Currently selected operation
     * @param array  $properties   Additional HTML properties
     */
    public function __construct(string $name, string $defaultValue = '', array $properties = [])
    {
        parent::__construct(
            $name,
            [
                'any' => _('Any'),
                'create' => _('Create'),
                'update' => _('Update'),
                'delete' => _('Delete'),
            ],
            $defaultValue,
            $properties,
        );
    }
}
