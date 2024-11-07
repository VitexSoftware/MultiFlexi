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
use MultiFlexi\CompanyApp;

class CompanyAppsBar extends \Ease\Html\DivTag
{
    public function __construct(Company $company, $properties = [])
    {
        parent::__construct('', []);
        $companyApps = (new CompanyApp($company))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.uuid'], true)->fetchAll();

        foreach ($companyApps as $companyApp) {
            $this->addItem(new \Ease\Html\ATag('companyapp.php?company_id='.$company->getMyKey().'&app_id='.$companyApp['id'], new \Ease\Html\ImgTag('appimage.php?uuid='.$companyApp['uuid'], _($companyApp['name']), ['title' => _($companyApp['description']), 'height' => '100px', 'style' => 'padding: 5px; margin: 5px;', 'class' => 'btn btn-secondary'])));
        }
    }
}
