<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Company')));
$companies = new Company(WebPage::getRequestValue('id', 'int'));
$_SESSION['company'] = &$companies;
$companyEnver = new \AbraFlexi\MultiFlexi\CompanyEnv($companies->getMyKey());
$jobber = new \AbraFlexi\MultiFlexi\Job();
$jobs = $jobber->listingQuery()->select(['apps.nazev AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'appcompany.id AS appcompanyid'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->leftJoin('appcompany ON appcompany.company_id = job.company_id AND appcompany.app_id = job.app_id')->where('job.company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('Launch now'), _('Launch in Background')]);
foreach ($jobs as $job) {
    $job['launch'] = new \Ease\TWB4\LinkButton('launch.php?id='.$job['appcompanyid'].'&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg');
    $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?id='.$job['appcompanyid'].'&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Schedule') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
    $job['appimage'] = new ATag('companyapp.php?id=' . $job['appcompanyid'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag($job['appimage'], $job['appname'], ['height' => 50, 'title' => $job['appname']]), '&nbsp;', $job['appname']])]);
    unset($job['appname']);
    unset($job['appcompanyid']);
    unset($job['app_id']);
    $job['id'] = new ATag('job.php?id=' . $job['id'], new \Ease\TWB4\Badge('info', $job['id']));
    $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
    $job['exitcode'] = new ExitCode($job['exitcode']);
    $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer');
    unset($job['login']);
    $jobList->addRowColumns($job);
}


$companyPanelContents = [];
$headRow = new Row();
$logo = new \Ease\Html\ImgTag(strlen($companies->getDataValue('logo')) ? $companies->getDataValue('logo') : 'src\company.svg', 'logo', ['class' => 'img-fluid', 'min-width' => '100%']);
$headRow->addColumn(2, [$logo, '<p></p>', new \Ease\TWB4\LinkButton('companysetup.php?id=' . $companies->getMyKey(), '⚙&nbsp;' . _('Company setup'), 'primary btn-lg btn-block '), '<p></p>', new \Ease\TWB4\LinkButton('tasks.php?company_id=' . $companies->getMyKey(), _('Setup tasks'), 'primary btn-lg btn-block')]);
$headRow->addColumn(10, new EnvironmentView($companyEnver->getData()));
$companyPanelContents[] = $headRow;
$companyPanelContents[] = new \Ease\Html\HrTag();
$companyPanelContents[] = $jobList;
$bottomLine = new Row();
$oPage->container->addItem(new Panel($companies->getRecordName(), 'light',
                $companyPanelContents, $bottomLine));
$oPage->addItem(new PageBottom());
$oPage->draw();
