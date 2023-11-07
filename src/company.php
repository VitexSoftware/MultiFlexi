<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
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
$jobs = $jobber->listingQuery()->select(['apps.name AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'runtemplate.id AS runtemplateid'], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->leftJoin('runtemplate ON runtemplate.company_id = job.company_id AND runtemplate.app_id = job.app_id')->where('job.company_id', $companies->getMyKey())->limit(20)->orderBy('job.id DESC')->fetchAll();
$jobList = new \Ease\TWB4\Table();
$jobList->addRowHeaderColumns([_('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('Launch now'), _('Launch in Background')]);
foreach ($jobs as $job) {
    $job['launch'] = new \Ease\TWB4\LinkButton('launch.php?id=' . $job['runtemplateid'] . '&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg');
    // use AppLaunchForm instead of LaunchButton
    //    $job['launch'] = new AppLaunchForm($job['app_id'], $companies->getMyKey());

    $job['schedule'] = new \Ease\TWB4\LinkButton('schedule.php?id=' . $job['runtemplateid'] . '&app_id=' . $job['app_id'] . '&company_id=' . $companies->getMyKey(), [_('Schedule') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');
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
    $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer');
    unset($job['login']);
    $jobList->addRowColumns($job);
}


$companyPanelContents = [];
$headRow = new Row();
$logo = new \Ease\Html\ImgTag(empty($companies->getDataValue('logo')) ? 'src\company.svg' : $companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid', 'min-width' => '100%']);
$deleteButton = new \Ease\TWB4\LinkButton('companydelete.php?id=' . $companies->getMyKey(), '☠️&nbsp;' . _('Delete company'), 'danger');
$headRow->addColumn(2, [$logo, '<p></p>', new \Ease\TWB4\LinkButton('companysetup.php?id=' . $companies->getMyKey(), '🛠️&nbsp;' . _('Company setup'), 'primary btn-lg btn-block '), '<p></p>', new \Ease\TWB4\LinkButton('tasks.php?company_id=' . $companies->getMyKey(), '🔧&nbsp;' . _('Setup tasks'), 'primary btn-lg btn-block'), '<p></p>', $deleteButton]);
$headRow->addColumn(10, new EnvironmentView($companyEnver->getData()));
$companyPanelContents[] = $headRow;
$companyPanelContents[] = new \Ease\Html\HrTag();
$companyPanelContents[] = $jobList;
$bottomLine = new Row();
$oPage->container->addItem(new Panel(
    $companies->getRecordName(),
    'light',
    $companyPanelContents,
    $bottomLine
));
$oPage->addItem(new PageBottom());
$oPage->draw();
