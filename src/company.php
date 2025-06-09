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

require_once './init.php';
WebPage::singleton()->onlyForLogged();
$companies = new Company(WebPage::getRequestValue('id', 'int'));
WebPage::singleton()->addItem(new PageTop(_('Company').': '.$companies->getRecordName()));

$_SESSION['company'] = $companies->getMyKey();
$_SESSION['server'] = $companies->getDataValue('server');
$_SESSION['customer'] = $companies->getDataValue('customer');

$companyEnver = new \MultiFlexi\CompanyEnv($companies->getMyKey());
$jobber = new \MultiFlexi\Job();
$jobber->setDataValue('company_id', $companies->getMyKey());

$showOnly = WebPage::getRequestValue('showOnly');

switch ($showOnly) {
    case 'failed':
        $condition['exitcode'] = [255, 128, 1];

        break;
    case 'unfinished':
        $condition['exitcode'] = null;

        break;
    case 'success':
        $condition['exitcode'] = 0;

        break;

    default:
        $condition = [];

        break;
}

$jobs = $jobber->listingQuery()->select(['apps.name AS appname', 'apps.uuid AS uuid', 'job.id', 'begin', 'exitcode', 'launched_by', 'job.executor', 'job.schedule_type', 'login', 'job.app_id AS app_id', 'runtemplate.id AS runtemplate_id', 'runtemplate.name AS runtemplate_name', 'schedule AS scheduled'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')->where('job.company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->where($condition)->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Run template').' / '._('Application'), _('Job ID'), _('Launch time'), _('Launcher'), _('Schedule Launch')]);

foreach ($jobs as $job) {
    $job['appname'] = '<strong>'.(empty($job['runtemplate_name']) ? '#'.$job['runtemplate_id'] : $job['runtemplate_name']).'</strong>&nbsp;/&nbsp;'.$job['appname'];
    unset($job['runtemplate_name']);
    //    $job['launch'] = new \Ease\TWB4\LinkButton('launch.php?id='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg');

    if ($job['begin']) {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?id='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'light btn-block');
    } else {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?cancel='.$job['id'].'&templateid='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Cancel').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/cancel.svg', _('Cancel'), ['height' => '30px'])], 'warning');
    }

    // Přidejte styl white-space: nowrap k badge s názvem aplikace
    $job['uuid'] = new ATag(
        'runtemplate.php?id='.$job['runtemplate_id'],
        [
            new \Ease\TWB4\Badge(
                'light',
                [
                    new \Ease\Html\ImgTag('appimage.php?uuid='.$job['uuid'], $job['appname'], ['height' => 50, 'title' => $job['appname']]),
                    '&nbsp;',
                    // Přidejte styl pro nezalamování názvu aplikace
                    new \Ease\Html\SpanTag($job['appname'], ['style' => 'white-space: nowrap;'])
                ],
                ['style' => 'white-space: nowrap;']
            )
        ]
    );
    unset($job['appname'], $job['runtemplate_id'], $job['app_id']);

    $job['id'] = new \Ease\Html\ATag('job.php?id='.$job['id'], [new ExitCode($job['exitcode'], ['style' => 'font-size: 1.0em; font-family: monospace;']), '<br>', new \Ease\TWB4\Badge('info', '🏁 '.$job['id'])], ['title' => _('Job Info')]);

    if ($job['begin']) {
        $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))];
    } else {
        $job['begin'] = '⏳&nbsp;'._('Scheduled').($job['scheduled'] ? new \Ease\Html\DivTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['scheduled']))) : '');
    }

    $job['launched_by'] = [
        new ExecutorImage($job['executor'], ['align' => 'right', 'height' => '50px']),
        new \Ease\Html\DivTag($job['launched_by'] ? new \Ease\Html\ATag('user.php?id='.$job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer')),
        new \Ease\Html\DivTag(\MultiFlexi\RunTemplate::getIntervalEmoji(\MultiFlexi\RunTemplate::intervalToCode((string) $job['schedule_type'])).'&nbsp;'.$job['scheduled']),
    ];
    unset($job['executor'], $job['scheduled'], $job['login'], $job['schedule_type'], $job['exitcode']);

    $jobList->addRowColumns($job);
}

$companyPanelContents[] = new CompanyAppsBar($companies);
$companyJobChart = new CompanyJobChart($jobber, ['id' => 'container']);

$jobber = new \MultiFlexi\Job();

$width = 200;
$height = 200;

$todaysJobs = $jobber->listingQuery()->select('exitcode', true)->limit($width * $height)->orderBy('id')->where('company_id', $companies->getMyKey())->fetchAll();

$jobGraph = new JobGraph($width, $height, $todaysJobs);
$jobGraph->calcultateStats();

$imageTag = $jobGraph->getImageTag($companies->getMyKey());

// Calculate percentages
$totalJobs = $jobGraph->getTotalJobs();
$successCount = $jobGraph->getSuccessCount();
$failureCount = $jobGraph->getFailureCount();
$noExecutableCount = $jobGraph->getNoExecutableCount();
$exceptionCount = $jobGraph->getExceptionCount();
$waitingCount = $jobGraph->getWaitingCount();
$successPercentage = ($totalJobs > 0) ? ($successCount / $totalJobs) * 100 : 0;
$failurePercentage = ($totalJobs > 0) ? ($failureCount / $totalJobs) * 100 : 0;
$noExecutablePercentage = ($totalJobs > 0) ? ($noExecutableCount / $totalJobs) * 100 : 0;
$exceptionPercentage = ($totalJobs > 0) ? ($exceptionCount / $totalJobs) * 100 : 0;
$waitingPercentage = ($totalJobs > 0) ? ($waitingCount / $totalJobs) * 100 : 0;

// Create the legend
$legend = new \Ease\Html\DivTag(
    [
        new \Ease\Html\DivTag(new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 255, 0);']).' Success: '.$successCount.' ('.number_format($successPercentage, 2).'%)'),
        new \Ease\Html\DivTag(new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(255, 0, 0); margin-left: 10px;']).' Failure: '.$failureCount.' ('.number_format($failurePercentage, 2).'%)'),
        new \Ease\Html\DivTag(new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(255, 255, 0); margin-left: 10px;']).' No Executable: '.$noExecutableCount.' ('.number_format($noExecutablePercentage, 2).'%)'),
        new \Ease\Html\DivTag(new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 0, 0); margin-left: 10px;']).' Exception: '.$exceptionCount.' ('.number_format($exceptionPercentage, 2).'%)'),
        new \Ease\Html\DivTag(new \Ease\Html\SpanTag('■', ['style' => 'color: rgb(0, 0, 255); margin-left: 10px;']).' Waiting: '.$waitingCount.' ('.number_format($waitingPercentage, 2).'%)'),
        new \Ease\Html\DivTag('Total Jobs: '.$totalJobs, ['style' => 'margin-top: 10px;']),
    ],
    ['style' => 'margin-top: 20px;'],
);

$graphRow = new \Ease\TWB4\Row();
$graphRow->addColumn(2, [$imageTag, $legend]);
$graphRow->addColumn(10, $companyJobChart);

$companyPanelContents[] = $graphRow;

$companyPanelContents[] = new \Ease\Html\H3Tag(_('job queue'));
$companyPanelContents[] = $jobList;
$bottomLine = new CompanyDbStatus($companies);

WebPage::singleton()->container->addItem(new CompanyPanel($companies, $companyPanelContents, $bottomLine));
WebPage::singleton()->addItem(new PageBottom('company/'.$companies->getMyKey()));
WebPage::singleton()->draw();
