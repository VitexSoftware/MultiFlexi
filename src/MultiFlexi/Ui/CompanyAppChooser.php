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

use MultiFlexi\Company;

/**
 * Description of CompanyAppChooser.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CompanyAppChooser extends \Ease\Html\SelectTag
{
    /**
     * Choose from applications Assigned to given company.
     *
     * @param string                $name       form input name
     * @param array<string, string> $properties
     */
    public function __construct(string $name, Company $company, string $defaultValue = '', array $properties = [])
    {
        $companyApp = new \MultiFlexi\CompanyApp($company);
        $assignedRaw = $companyApp->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select('apps.name')->fetchAll('app_id');

        foreach ($assignedRaw as $appId => $appProperties) {
            $assignedRaw[$appId] = $appProperties['name'];
        }

        $assigned = empty($assignedRaw) ? [] : $assignedRaw;

        parent::__construct($name, $assigned, $defaultValue, $properties);
    }
}
