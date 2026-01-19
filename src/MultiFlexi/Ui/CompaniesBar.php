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

use Ease\Html\ATag;
use MultiFlexi\Company;
use MultiFlexi\CompanyApp;

/**
 * @no-named-arguments
 */
class CompaniesBar extends \Ease\Html\DivTag
{
    public function __construct(array $properties = [])
    {
        parent::__construct('', []);
        $companer = new Company();
        $companies = $companer->listingQuery()->fetchAll();

        $cardGroup = new \Ease\Html\DivTag(null, ['class' => 'card-group']);

        $jobber = new \MultiFlexi\Job();

        foreach ($companies as $companyData) {
            $todaysJobs = $jobber->listingQuery()->select(['exitcode'], true)->where($jobber->todaysCond())->where('company_id', $companyData['id']);

            $companer->setData($companyData);

            $companyAppCard = new \Ease\TWB4\Card(new JobSuccessGraph($todaysJobs, ['style' => 'max-width: fit-content; margin-left: auto; margin-right: auto;']), ['style' => 'width: 10rem;']);
            $companyAppCard->addTagClass('text-center');

            $companyAppCard->addItem(new ATag('company.php?id='.$companyData['id'], new \Ease\Html\ImgTag(empty($companyData['logo']) ? 'images/company.svg' : $companyData['logo'] , (string) $companyData['name'], ['title' => $companyData['slug'], 'class' => 'card-img-top', 'style' => 'padding: 5px; margin: 5px;max-height: 150px;max-width: 150px;'])));

            $companyAppCard->addItem(new \Ease\Html\DivTag(new \Ease\Html\H5Tag($companyData['name'], ['class' => 'card-title']), ['class' => 'card-body']));

            $companyAppStatus = new \Ease\Html\DivTag(null, ['style' => 'display: flex; flex-wrap: wrap; justify-content: center;']);

            $companyApps = (new CompanyApp($companer))->getAssigned()->leftJoin('apps ON apps.id = companyapp.app_id')->select(['apps.name', 'apps.description', 'apps.id', 'apps.uuid'], true)->fetchAll();

            foreach ($companyApps as $companyApp) {
                $appId = $companyApp['id'];
                $companyId = $companer->getMyKey();
                $counts = $jobber->listingQuery()->select(null)->select('SUM(CASE WHEN exitcode = 0 THEN 1 ELSE 0 END) AS success, SUM(CASE WHEN exitcode <> 0 AND exitcode IS NOT NULL THEN 1 ELSE 0 END) AS failed, SUM(CASE WHEN exitcode IS NULL THEN 1 ELSE 0 END) as waiting')->where($jobber->todaysCond('schedule'))->where('company_id', $companyId)->where('app_id', $appId)->fetch();
                $successJobs = (int) $counts['success'];
                $failedJobs = (int) $counts['failed'];
                $waitingJobs = (int) $counts['waiting'];
                $jobCounts = new \Ease\Html\DivTag(null, ['style' => 'display: flex; justify-content: center; gap: 2px;']);

                if ($successJobs > 0) {
                    $jobCounts->addItem(new ATag('joblist.php?app_id='.$appId.'&company_id='.$companyId.'&status=success', (string) $successJobs, ['class' => 'badge badge-pill badge-success']));
                }

                if ($failedJobs > 0) {
                    $jobCounts->addItem(new ATag('joblist.php?app_id='.$appId.'&company_id='.$companyId.'&status=failed', (string) $failedJobs, ['class' => 'badge badge-pill badge-danger']));
                }

                if ($waitingJobs > 0) {
                    $jobCounts->addItem(new ATag('joblist.php?app_id='.$appId.'&company_id='.$companyId.'&status=waiting', (string) $waitingJobs, ['class' => 'badge badge-pill badge-warning']));
                }

                $appIcon = new ATag('companyapp.php?company_id='.$companer->getMyKey().'&app_id='.$appId, new \Ease\Html\ImgTag('appimage.php?uuid='.$companyApp['uuid'], _($companyApp['name']), ['title' => _($companyApp['description']), 'style' => 'padding: 5px; margin: 5px;max-height: 50px;max-width: 50px;']));
                $appBlock = new \Ease\Html\DivTag($appIcon);

                if ($successJobs > 0 || $failedJobs > 0 || $waitingJobs > 0) {
                    $appBlock->addItem($jobCounts);
                }

                $companyAppStatus->addItem($appBlock);
            }

            $companyAppCard->addItem(new \Ease\Html\DivTag($companyAppStatus, ['class' => 'card-footer  bg-transparent']));

            $cardGroup->addItem($companyAppCard);
        }

        $this->addItem($cardGroup);
    }
}
