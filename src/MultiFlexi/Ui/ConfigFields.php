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
 * Description of ConfigFields.
 *
 * @author vitex
 */
class ConfigFields extends \Ease\Html\SelectTag
{
    public function __construct($name, $defaultValue = null, $properties = [])
    {
        parent::__construct(
            $name,
            [
                'text' => _('Text'),
                'number' => _('Number'),
                'date' => _('Date'),
                'email' => _('Email'),
                'password' => _('Password'),
                'checkbox' => _('Yes/No'),
                'file' => _('File upload'),
                'directory' => _('Directory path'),
            ],
            $defaultValue,
            $properties,
        );
    }
}
