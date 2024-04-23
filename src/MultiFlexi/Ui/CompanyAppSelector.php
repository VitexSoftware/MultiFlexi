<?php

declare(strict_types=1);

/**
 * Multi Flexi - Select for Apps already assigned to company
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\AbraFlexi\Company;
use MultiFlexi\CompanyApp;

/**
 * Description of CompanyAppSelector
 *
 * @author vitex
 */
class CompanyAppSelector extends AppsSelector
{
    protected $companyId;
    public function __construct($company, $identifier = null, $enabled = [], $optionsPage = 'apps.php')
    {
        $this->companyId = $company->getMyKey();
        parent::__construct($identifier, $enabled, $optionsPage);
    }

    public function availbleApps()
    {
        return (new CompanyApp(new Company($this->companyId)))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name','apps.description','apps.id','apps.image'], true)->fetchAll();
    }
}
