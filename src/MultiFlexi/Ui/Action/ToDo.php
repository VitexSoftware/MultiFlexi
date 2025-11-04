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

namespace MultiFlexi\Ui\Action;

use MultiFlexi\Ui\CredentialSelect;

/**
 * ToDo Action UI Class.
 *
 * @author vitex
 */
class ToDo
{
    /**
     * Generate configuration form inputs for ToDo action.
     *
     * @param string $prefix Form field prefix
     *
     * @return \Ease\Embedable Form field(s)
     */
    public static function inputs(string $prefix): \Ease\Embedable
    {
        $companyId = 0;

        if (isset($_SESSION) && \array_key_exists('company', $_SESSION)) {
            $companyId = $_SESSION['company'];
        }

        return new \Ease\TWB4\FormGroup(
            _('Office365 Credential'),
            new CredentialSelect($prefix.'[ToDo][credential]', $companyId, 'Office365'),
            '',
            _('Select Office365 credential for ToDo integration'),
        );
    }
}
