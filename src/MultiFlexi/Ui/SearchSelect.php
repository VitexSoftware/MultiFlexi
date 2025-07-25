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
 * Description of SearchSelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 *
 * @no-named-arguments
 */
class SearchSelect extends \Ease\Html\SelectTag
{
    public function loadItems(): array
    {
        return [
            'RunTemplate' => _('RunTemplate'),
            'Job' => _('Job'),
            'Application' => _('Application'),
            'Company' => _('Company'),
            'Credential' => _('Credential'),
            'CredentialType' => _('Credential Type'),
            'all' => _('All').' ('._('slow').')',
        ];
    }
}
