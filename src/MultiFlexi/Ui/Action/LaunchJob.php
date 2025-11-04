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

/**
 * LaunchJob Action UI Class.
 *
 * @author vitex
 */
class LaunchJob
{
    /**
     * Generate configuration form inputs for LaunchJob action.
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

        return new \MultiFlexi\Ui\CompanyAppChooser($prefix.'[LaunchJob][jobid]', new \MultiFlexi\Company($companyId));
    }
}
