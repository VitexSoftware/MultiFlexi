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
$oPage->onlyForLogged();
$companies = new Company(WebPage::getRequestValue('id', 'int'));
$oPage->addItem(new PageTop(_('Company').': '.$companies->getRecordName()));

$oPage->container->addItem(new \Ease\TWB4\LinkButton('wizard.php?company='.$companies->getMyKey(), _('New Configuration wizard').' 🧙🏽‍♂️', 'outline-dark'));

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
        $condition['exitcode'] = -1;

        break;
    case 'success':
        $condition['exitcode'] = 0;

        break;

    default:
        $condition = [];

        break;
}

$jobs = $jobber->listingQuery()->select(['apps.name AS appname', 'apps.uuid AS uuid', 'job.id', 'begin', 'exitcode', 'launched_by', 'job.executor', 'login', 'job.app_id AS app_id', 'runtemplate.id AS runtemplate_id', 'runtemplate.name AS runtemplate_name', 'schedule AS scheduled'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')->where('job.company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->where($condition)->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Run template').' / '._('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('Schedule Launch')]);

foreach ($jobs as $job) {
    $job['appname'] = '<strong>'.(empty($job['runtemplate_name']) ? '#'.$job['runtemplate_id'] : $job['runtemplate_name']).'</strong>&nbsp;/&nbsp;'.$job['appname'];
    unset($job['runtemplate_name']);
    //    $job['launch'] = new \Ease\TWB4\LinkButton('launch.php?id='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg');

    if ($job['begin']) {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?id='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
    } else {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?cancel='.$job['id'].'&templateid='.$job['runtemplate_id'].'&app_id='.$job['app_id'].'&company_id='.$companies->getMyKey(), [_('Cancel').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/cancel.svg', _('Cancel'), ['height' => '30px'])], 'danger btn-lg');
    }

    $job['uuid'] = new ATag('runtemplate.php?id='.$job['runtemplate_id'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag('appimage.php?uuid='.$job['uuid'], $job['appname'], ['height' => 50, 'title' => $job['appname']]), '&nbsp;', $job['appname']])]);
    unset($job['appname'], $job['runtemplate_id'], $job['app_id']);

    $job['id'] = new ATag('job.php?id='.$job['id'], new \Ease\TWB4\Badge('info', $job['id']));

    if ($job['begin']) {
        $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\Html\WidgetsLiveAge((new \DateTime($job['begin']))->getTimestamp()))];
    } else {
        $job['begin'] = _('Scheduled');
    }

    $job['exitcode'] = new ExitCode($job['exitcode']);
    $job['launched_by'] = [
        new ExecutorImage($job['executor'], ['align' => 'right', 'height' => '50px']),
        new \Ease\Html\DivTag($job['launched_by'] ? new \Ease\Html\ATag('user.php?id='.$job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer')),
        new \Ease\Html\DivTag($job['scheduled']),
    ];
    unset($job['executor'], $job['scheduled'], $job['login']);

    $jobList->addRowColumns($job);
}

$companyPanelContents[] = new CompanyAppsBar($companies);
$companyPanelContents[] = new CompanyJobChart($jobber, ['id' => 'container']);
$companyPanelContents[] = new \Ease\Html\H3Tag(_('job queue'));
$companyPanelContents[] = $jobList;
$bottomLine = new CompanyDbStatus($companies);

$oPage->container->addItem(new CompanyPanel($companies, $companyPanelContents, $bottomLine));
$oPage->addItem(new PageBottom());
$oPage->draw();
