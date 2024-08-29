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
 * Description of CompanyAppChooser.
 *
 * @author vitex
 */
class CompanyAppChooser extends \Ease\Html\SelectTag
{
    /**
     * Choose from applications Assigned to given company.
     *
     * @param string             $name         form input name
     * @param \AbraFlexi\Company $company
     * @param string             $defaultValue
     * @param array              $properties
     */
    public function __construct($name, $company, $defaultValue = null, $properties = [])
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
