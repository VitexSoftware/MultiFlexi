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
use Ease\Html\DivTag;
use Ease\Html\H3Tag;
use Ease\Html\ImgTag;
use Ease\Html\SmallTag;
use Ease\Html\SpanTag;
use Ease\TWB4\LinkButton;
use Ease\TWB4\Row;
use Ease\TWB4\Table;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\Job;
use MultiFlexi\RunTemplate;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$companer = new Company(WebPage::getRequestValue('company_id', 'int'));
$application = new Application(WebPage::getRequestValue('app_id', 'int'));

WebPage::singleton()->addItem(new PageTop(_($application->getRecordName()).'@'.$companer->getRecordName()));
// $companyApp = new \MultiFlexi\RunTemplate(\Ease\Document::getRequestValue('id', 'int'));
// $appData = $companyApp->getAppInfo();
// $companies = new Company($companyApp->getDataValue('company_id'));
// if (strlen($companies->getDataValue('logo'))) {
//    $companyTasksHeading[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid','style' => 'height']);
// }

$companyTasksHeading[] = new SpanTag($companer->getDataValue('name').'&nbsp;', ['style' => 'font-size: xxx-large;']);
$companyTasksHeading[] = _('Assigned applications');

$runTemplater = new RunTemplate();
$runtemplatesRaw = $runTemplater->listingQuery()->where('app_id', $application->getMyKey())->order('name')->where('company_id', $companer->getMyKey());

$runtemplatesDiv = new DivTag();
$runtemplates = [];

$jobber = new Job();

$oPage->addCss(<<<'EOD'

.runtemplate {
background-color: #0001;
}
.runtemplate:hover {
background-color: #0002;
}

EOD);

if ($runtemplatesRaw->count()) {
    foreach ($runtemplatesRaw as $runtemplateData) {
        // TODO:    $app->checkRequiredFields($_POST, true);

        $runtemplateRow = new Row(null, ['class' => 'runtemplate']);
        $lastJobRun = $jobber->listingQuery()->select(['exitcode', 'id'], true)->where(['runtemplate_id' => $runtemplateData['id']])->orderBy('id desc')->limit(1);
        $runtemplateRow->addColumn(2, new ATag('runtemplate.php?id='.$runtemplateData['id'], '⚗️&nbsp;#'.(string) $runtemplateData['id']));

        if (\count($lastJobRun)) {
            $lastJobRunData = $lastJobRun->fetch();
            $runtemplateRow->addColumn(2, new ATag('job.php?id='.$lastJobRunData['id'], new ExitCode($lastJobRunData['exitcode'])));
        } else {
            $runtemplateRow->addColumn(2, new ExitCode(-1));
        }

        $successIcons = new ATag('actions.php?id='.$runtemplateData['id'].'#SuccessActions', RunTemplate::actionIcons($runtemplateData['success'] ? unserialize($runtemplateData['success']) : null, ['style' => 'border-bottom: 4px solid green;']));
        $failIcons = new ATag('actions.php?id='.$runtemplateData['id'].'#FailActions', RunTemplate::actionIcons($runtemplateData['fail'] ? unserialize($runtemplateData['fail']) : null, ['style' => 'border-bottom: 4px solid red;']));

        $runtemplateRow->addColumn(8, ['<span title="'._(RunTemplate::codeToInterval($runtemplateData['interv'])).'">'.RunTemplate::getIntervalEmoji($runtemplateData['interv']).'</span>&nbsp;', $runtemplateData['active'] ? '&nbsp;<a href="schedule.php?id='.$runtemplateData['id'].'&when=now&executor=Native" title="'._('Launch now').'"><span style="color: green; font-weight: xx-large;">▶</span></a> ' : '<span style="color: lightgray; font-weight: xx-large;" title="'._('Disabled').'">🚧</span>', '&nbsp;', new ATag('runtemplate.php?id='.$runtemplateData['id'], $runtemplateData['name']), '&nbsp;&nbsp;', $successIcons, '&nbsp;', $failIcons]);
        $runtemplatesDiv->addItem($runtemplateRow);
        $runtemplates[$runtemplateData['id']] = $runtemplateData['name'];
    }
}

$runtemplatesDiv->addItem(new \Ease\Html\PTag(new DivTag(new \Ease\Html\HrTag())));
$runtemplatesDiv->addItem(new LinkButton('runtemplate.php?new=1&app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(), '⚗️&nbsp;➕'._('new'), 'success'));

$jobs = $jobber->listingQuery()->select(['job.id', 'begin', 'exitcode', 'launched_by', 'login', 'runtemplate_id'], true)->leftJoin('user ON user.id = job.launched_by')->where('company_id', $companer->getMyKey())->where('app_id', $application->getMyKey())->limit(10)->orderBy('job.id DESC')->fetchAll();
$jobList = new Table();
$jobList->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('RunTemplate')]);

foreach ($jobs as $job) {
    $job['id'] = new ATag('job.php?id='.$job['id'], '🏁 '.$job['id']);

    if (empty($job['begin'])) {
        $job['begin'] = _('Not launched yet');
    } else {
        $job['begin'] = [$job['begin'], ' ', new SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))];
    }

    $job['exitcode'] = new ExitCode($job['exitcode']);
    $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id='.$job['launched_by'], $job['login']) : _('Timer');

    if (\array_key_exists($job['runtemplate_id'], $runtemplates)) {
        $job['runtemplate_id'] = new ATag('runtemplate.php?id='.$job['runtemplate_id'], $runtemplates[$job['runtemplate_id']]);
    }

    unset($job['login']);
    $jobList->addRowColumns($job);
}

$historyButton = (new LinkButton('joblist.php?app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(), _('Job History').' '.new ImgTag('images/log.svg', _('Set'), ['height' => '30px']), 'info btn-sm  btn-block'));

$companyAppColumns = new Row();
$companyAppColumns->addColumn(6, $runtemplatesDiv);
$companyAppColumns->addColumn(6, [new H3Tag(_('Last 10 jobs')), $jobList, $historyButton]);

$companyApp = (new \MultiFlexi\CompanyApp($companer))->setApp($application);

$companyAppJobChart = new CompanyAppJobsLastMonthChart($companyApp);

WebPage::singleton()->container->addItem(new CompanyPanel($companer, new CompanyApplicationPanel($companyApp, $companyAppColumns, $companyAppJobChart)));

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
