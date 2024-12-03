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
 * Description of CompanyRuntemplatesLinks.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CompanyRuntemplatesLinks extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\Company $company, \MultiFlexi\Application $application, array $properties = [], array $linkProperties = [])
    {
        $runTemplater = new \MultiFlexi\RunTemplate();
        $runtemplatesRaw = $runTemplater->listingQuery()->where('app_id', $application->getMyKey())->where('company_id', $company->getMyKey());
        $jobber = new \MultiFlexi\Job();

        $runtemplatesDiv = new \Ease\Html\DivTag();

        if ($runtemplatesRaw->count()) {
            foreach ($runtemplatesRaw as $runtemplateData) {
                $linkProperties['title'] = $runtemplateData['name'];
                $lastJobInfo = $jobber->listingQuery()->select(['id', 'exitcode'], true)->where(['company_id' => $company->getMyKey(), 'app_id' => $runtemplateData['app_id']])->order('id DESC')->limit(1)->fetchAll();

                if ($lastJobInfo) {
                    $companyAppStatus = new \Ease\Html\ATag('job.php?id='.$lastJobInfo[0]['id'], new ExitCode($lastJobInfo[0]['exitcode'], ['style' => 'font-size: 1.0em; font-family: monospace;']), ['class' => 'btn btn-outline-secondary btn-sm']);
                } else {
                    $companyAppStatus = new \Ease\TWB4\Badge('disabled', '🪤', ['style' => 'font-size: 0.5em; font-family: monospace;']);
                }

                $runtemplatesDiv->addItem(new \Ease\Html\SpanTag([new \Ease\Html\ATag('runtemplate.php?id='.$runtemplateData['id'], '⚗️#'.$runtemplateData['id'], ['class' => 'btn btn-outline-secondary btn-sm']), $companyAppStatus], ['class' => 'btn-group', 'style' => 'margin: 2px', 'role' => 'group']));
            }
        } else {
            $runtemplatesDiv->addItem(new \Ease\Html\ATag('runtemplate.php?new=1&app_id='.$application->getMyKey().'&company_id='.$company->getMyKey(), '➕'));
        }

        parent::__construct($runtemplatesDiv, $properties);
    }

    public function count(): int
    {
        return $this->pageParts[0]->getItemsCount();
    }
}
