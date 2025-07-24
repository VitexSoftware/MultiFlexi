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

/**
 * @no-named-arguments
 */
class CompanyAppsBar extends \Ease\Html\DivTag
{
    public function __construct(Company $company, $properties = [])
    {
        parent::__construct('', []);
        $companyApps = (new CompanyApp($company))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.uuid'], true)->fetchAll();

        $jobber = new \MultiFlexi\Job();

        $cardGroup = new \Ease\Html\DivTag(null, ['class' => 'card-group']);

        foreach ($companyApps as $companyApp) {
            $companyAppCard = new \Ease\TWB4\Card(new \Ease\Html\ATag('companyapp.php?company_id='.$company->getMyKey().'&app_id='.$companyApp['id'], new \Ease\Html\ImgTag('appimage.php?uuid='.$companyApp['uuid'], _($companyApp['name']), ['title' => _($companyApp['description']), 'class' => 'card-img-top', 'style' => 'padding: 5px; margin: 5px;max-height: 150px;max-width: 150px;'])), ['style' => 'width: 10rem; min-width: 120px;']);
            $companyAppCard->addTagClass('text-center');
            $lastJobInfo = $jobber->listingQuery()->select(['id', 'exitcode'], true)->where(['company_id' => $company->getMyKey(), 'app_id' => $companyApp['id']])->order('id DESC')->limit(1)->fetchAll();

            if ($lastJobInfo) {
                $companyAppStatus = new \Ease\Html\ATag('job.php?id='.$lastJobInfo[0]['id'], new ExitCode($lastJobInfo[0]['exitcode'], ['style' => 'font-size: 2.0em; font-family: monospace;']));
            } else {
                $companyAppStatus = new \Ease\TWB4\Badge('disabled', '🪤', ['style' => 'font-size: 2.0em; font-family: monospace;']);
            }

            $companyAppCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag(_($companyApp['name']), ['class' => 'card-title']), ['class' => 'card-body']));
            $companyAppCard->addItem(new \Ease\Html\DivTag($companyAppStatus, ['class' => 'card-footer  bg-transparent']));

            $cardGroup->addItem($companyAppCard);
        }

        $this->addItem($cardGroup);
    }
}
