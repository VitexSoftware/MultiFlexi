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
 * Dashboard recent jobs table widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2024 Vitex Software
 */
class DashboardRecentJobsTable extends \Ease\Html\DivTag
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->addItem(new \Ease\Html\H4Tag(_('Recent Jobs (Last 20)')));

        try {
            $jobber = new \MultiFlexi\Job();

            $recentJobs = $jobber->getFluentPDO()
                ->from('job')
                ->select('job.id, job.begin, job.end, job.exitcode, job.app_id, job.company_id, job.runtemplate_id, apps.name as app_name, company.name as company_name, runtemplate.name as runtemplate_name')
                ->leftJoin('apps ON apps.id = job.app_id')
                ->leftJoin('company ON company.id = job.company_id')
                ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')
                ->orderBy('job.begin DESC')
                ->limit(20)
                ->fetchAll();

            if (!empty($recentJobs)) {
                $table = new \Ease\TWB4\Table();
                $table->addRowHeaderColumns([_('ID'), _('Application'), _('Company'), _('RunTemplate'), _('Started'), _('Finished'), _('Status')]);

                foreach ($recentJobs as $job) {
                    $statusBadge = '';

                    if ($job['exitcode'] === null) {
                        if ($job['begin'] && !$job['end']) {
                            $statusBadge = new \Ease\TWB4\Badge('primary', '▶️ '._('Running'));
                        } else {
                            $statusBadge = new \Ease\TWB4\Badge('secondary', '⏳ '._('Pending'));
                        }
                    } elseif ((int) $job['exitcode'] === 0) {
                        $statusBadge = new \Ease\TWB4\Badge('success', '✓ '._('Success'));
                    } else {
                        $statusBadge = new \Ease\TWB4\Badge('danger', '✗ '._('Failed').' ('.$job['exitcode'].')');
                    }

                    // Create links with emoticons
                    $appLink = $job['app_id'] && $job['app_name']
                        ? new \Ease\Html\ATag('app.php?id='.$job['app_id'], '🧩 '.$job['app_name'])
                        : '-';
                    $companyLink = $job['company_id'] && $job['company_name']
                        ? new \Ease\Html\ATag('company.php?id='.$job['company_id'], '🏢 '.$job['company_name'])
                        : '-';
                    $runtemplateLink = $job['runtemplate_id'] && $job['runtemplate_name']
                        ? new \Ease\Html\ATag('runtemplate.php?id='.$job['runtemplate_id'], '⚗️️ '.$job['runtemplate_name'])
                        : '-';

                    $table->addRowColumns([
                        new \Ease\Html\ATag('job.php?id='.$job['id'], '🏁 '.$job['id']),
                        $appLink,
                        $companyLink,
                        $runtemplateLink,
                        $job['begin'] ? (new \DateTime($job['begin']))->format('Y-m-d H:i:s') : '-',
                        $job['end'] ? (new \DateTime($job['end']))->format('Y-m-d H:i:s') : '-',
                        $statusBadge,
                    ]);
                }

                $this->addItem($table);
            } else {
                $this->addItem(new \Ease\TWB4\Badge('info', _('No recent jobs')));
            }
        } catch (\Exception $e) {
            $this->addItem(new \Ease\TWB4\Badge('danger', _('Error loading recent jobs: ').$e->getMessage()));
        }
    }
}
