<?php

declare(strict_types=1);

/**
 * Multi Flexi - Select for Apps already assigned to comany
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Company;
use MultiFlexi\CompanyApp;

class CompanyAppsBar extends \Ease\TWB4\Container
{
    public function __construct(Company $company, $properties = [])
    {
        parent::__construct('', []);
        $companyApps = (new CompanyApp($company))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.image'], true)->fetchAll();
        foreach ($companyApps as $companyApp) {
            $this->addItem(new \Ease\Html\ATag('companyapp.php?company_id=' . $company->getMyKey() . '&app_id=' . $companyApp['id'], new \Ease\Html\ImgTag($companyApp['image'], _($companyApp['name']), ['title' => _($companyApp['description']), 'height' => '50px', 'style' => 'padding: 5px; margin: 5px;', 'class' => 'button' ])));
        }
    }
}
