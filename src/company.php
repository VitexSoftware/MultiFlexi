<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Row;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$companies = new Company(WebPage::getRequestValue('id', 'int'));
$oPage->addItem(new PageTop(_('Company') . ': ' . $companies->getRecordName()));

$_SESSION['company'] = $companies->getMyKey();
$_SESSION['server'] = $companies->getDataValue('server');
$_SESSION['customer'] = $companies->getDataValue('customer');

$companyEnver = new \MultiFlexi\CompanyEnv($companies->getMyKey());
$jobber = new \MultiFlexi\Job();
$jobber->setDataValue('company_id', $companies->getMyKey());
$jobs = $jobber->listingQuery()->select(['apps.name AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'job.executor', 'login', 'job.app_id AS app_id', 'runtemplate.id AS runtemplateid', 'schedule AS scheduled'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->leftJoin('runtemplate ON runtemplate.company_id = job.company_id AND runtemplate.app_id = job.app_id')->where('job.company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('Launch now'), _('Launch in Background')]);
foreach ($jobs as $job) {
    $job['appname'] = strlen($job['appname']) ? _($job['appname']) : 'n/a?!';
    $job['launch'] = new \Ease\TWB4\LinkButton('launch.php?id=' . $job['runtemplateid'] . '&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg');
    if ($job['begin']) {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?id=' . $job['runtemplateid'] . '&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Schedule') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
    } else {
        $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?cancel=' . $job['id'] . '&templateid=' . $job['runtemplateid'] . '&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Cancel') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/cancel.svg', _('Cancel'), ['height' => '30px'])], 'danger btn-lg');
    }

    $job['appimage'] = new ATag('companyapp.php?id=' . $job['runtemplateid'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag($job['appimage'], $job['appname'], ['height' => 50, 'title' => $job['appname']]), '&nbsp;', $job['appname']])]);
    unset($job['appname']);
    unset($job['runtemplateid']);
    unset($job['app_id']);
    $job['id'] = new ATag('job.php?id=' . $job['id'], new \Ease\TWB4\Badge('info', $job['id']));
    if ($job['begin']) {
        $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
    } else {
        $job['begin'] = _('Scheduled');
    }
    $job['exitcode'] = new ExitCode($job['exitcode']);
    $job['launched_by'] = [
        new ExecutorImage($job['executor'], ['align' => 'right','height' => '50px']),
        new \Ease\Html\DivTag($job['launched_by'] ? new \Ease\Html\ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer')),
        new \Ease\Html\DivTag($job['scheduled'])
    ];
    unset($job['executor']);
    unset($job['scheduled']);
    unset($job['login']);
    $jobList->addRowColumns($job);
}

$companyPanelContents[] = new CompanyJobChart($jobber, ['id' => 'container']);
$companyPanelContents[] = new \Ease\Html\H3Tag(_('job queue'));
$companyPanelContents[] = $jobList;
$bottomLine = new CompanyDbStatus($companies);

$oPage->container->addItem(new CompanyPanel($companies, $companyPanelContents, $bottomLine));
$oPage->addItem(new PageBottom());
$oPage->draw();
