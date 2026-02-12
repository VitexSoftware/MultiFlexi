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
 * Description of RuntemplateChooser.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class RuntemplateChooser extends \Ease\Html\SelectTag
{
    /**
     * Choose from applications Assigned to given company.
     *
     * @param string                $name       form input name
     * @param array<string, string> $properties
     */
    public function __construct(string $name, Company $company, string $defaultValue = '', array $properties = [])
    {
        $runTemplate = new \MultiFlexi\RunTemplate();
        $assignedRaw = $runTemplate->listingQuery()->where('company_id', $company->getMyKey())->orderBy('name')->fetchAll('id');

        foreach ($assignedRaw as $runtemplateId => $runtemplateProperties) {
            $assignedRaw[$runtemplateId] = $runtemplateProperties['name'];
        }

        $assigned = empty($assignedRaw) ? [] : $assignedRaw;

        parent::__construct($name, $assigned, $defaultValue, $properties);
    }
}
