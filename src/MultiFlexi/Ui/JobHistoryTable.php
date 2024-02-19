<?php

declare(strict_types=1);

/**
 * Multi Flexi - Show Last X jobs
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of JobHistory
 *
 * @author vitex
 */
class JobHistoryTable extends \Ease\TWB4\Table
{
    /**
     * Job History presented as table
     *
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct($content, $properties);
        $jobber = new \MultiFlexi\Job();
        $jobs = $jobber->listingQuery()->
                select(['apps.name AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'job.executor', 'job.company_id', 'company.name', 'company.logo', 'schedule'], true)
                ->leftJoin('apps ON apps.id = job.app_id')
                ->leftJoin('user ON user.id = job.launched_by')
                ->limit(50)
                ->where('begin IS NOT NULL')
                ->orderBy('job.id DESC')
                ->fetchAll();
        $this->addRowHeaderColumns([_('Application'), _('Exit Code') . '/' . _('Job ID'), _('Launch time'), _('Launcher'), _('Company')]);
        $company = new \AbraFlexi\Company();
        foreach ($jobs as $job) {
            $exitCode = $job['exitcode'];
            $company->setDataValue('logo', $job['logo']);
            $company->setDataValue('name', $job['name']);
            $job['appimage'] = new \Ease\Html\ATag('app.php?id=' . $job['app_id'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag($job['appimage'], _($job['appname']), ['height' => 60, 'title' => $job['appname']]), '&nbsp;', _($job['appname'])])]);
            unset($job['appname']);
            unset($job['app_id']);
            $job['id'] = new \Ease\Html\ATag('job.php?id=' . $job['id'], [new ExitCode($exitCode, ['style' => 'font-size: 1.0em; font-family: monospace;']), '<br>', new \Ease\TWB4\Badge('info', $job['id'])], ['title' => _('Job Info')]);
            $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
            unset($job['exitcode']);

            $job['launched_by'] = [
                new ExecutorImage($job['executor'], ['align' => 'right','height' => '50px']),
                new \Ease\Html\DivTag($job['launched_by'] ? new \Ease\Html\ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer')),
                new \Ease\Html\DivTag($job['schedule']),
                new \Ease\Html\DivTag($job['executor'])
            ];
            unset($job['executor']);
            unset($job['login']);
            unset($job['schedule']);
            $job['company_id'] = [new CompanyLogo($company, ['height' => '60px','align' => 'right']),  new \Ease\Html\ATag('company.php?id=' . $job['company_id'], $job['name'])];
            unset($job['name']);
            unset($job['logo']);
            $this->addRowColumns($job);
        }
    }
}
