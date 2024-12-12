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

class CompaniesBar extends \Ease\Html\DivTag
{
    public function __construct(array $properties = [])
    {
        parent::__construct('', []);
        $companer = new Company();
        $companies = $companer->listingQuery()->fetchAll();

        $cardGroup = new \Ease\Html\DivTag(null, ['class' => 'card-group']);

        foreach ($companies as $companyData) {
            $companer->setData($companyData);
            $companyAppCard = new \Ease\TWB4\Card(new \Ease\Html\ATag('company.php?id='.$companyData['id'], new \Ease\Html\ImgTag($companyData['logo'], $companyData['name'], ['title' => $companyData['code'], 'class' => 'card-img-top', 'style' => 'padding: 5px; margin: 5px;max-height: 150px;max-width: 150px;'])), ['style' => 'width: 10rem;']);
            $companyAppCard->addTagClass('text-center');

            $companyAppCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag($companyData['name'], ['class' => 'card-title']), ['class' => 'card-body']));
            
            
            
            
            $companyAppStatus = new \Ease\Html\DivTag();

            $companyApps = (new CompanyApp($companer))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.uuid'], true)->fetchAll();
            
            foreach ($companyApps as $companyApp){
                $companyAppStatus->addItem(new \Ease\Html\ATag('companyapp.php?company_id='.$companer->getMyKey().'&app_id='.$companyApp['id'], new \Ease\Html\ImgTag('appimage.php?uuid='.$companyApp['uuid'], _($companyApp['name']), ['title' => _($companyApp['description']),  'style' => 'padding: 5px; margin: 5px;max-height: 50px;max-width: 50px;'])));
            }
            
            $companyAppCard->addItem(new \Ease\Html\DivTag($companyAppStatus, ['class' => 'card-footer  bg-transparent']));

            $cardGroup->addItem($companyAppCard);
        }

        $this->addItem($cardGroup);
    }
}
