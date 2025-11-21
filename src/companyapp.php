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
use Ease\Html\H3Tag;
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

// Create CompanyApp object for chart
$companyApp = (new \MultiFlexi\CompanyApp($companer))->setApp($application);

// RunTemplates section with header and button
$runtemplatesDiv = new \Ease\Html\DivTag();

// Add chart above RunTemplates table
$runtemplatesDiv->addItem(new CompanyAppJobsLastMonthChart($companyApp));

$runtemplatesHeader = new Row(null, ['style' => 'margin-top: 20px;']);
$runtemplatesHeader->addColumn(9, new H3Tag('⚗️ '._('RunTemplates for this Company')));
$runtemplatesHeader->addColumn(3, new LinkButton(
    'runtemplate.php?new=1&app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(),
    '⚗️&nbsp;➕ '._('New RunTemplate'),
    'success btn-block'
));
$runtemplatesDiv->addItem($runtemplatesHeader);

// RunTemplates table
$runTemplater = new RunTemplate();
$runtemplatesRaw = $runTemplater->listingQuery()
    ->where('app_id', $application->getMyKey())
    ->where('company_id', $companer->getMyKey())
    ->orderBy('name');

$jobber = new Job();

if ($runtemplatesRaw->count()) {
    $rtTable = new Table(null, ['class' => 'table table-striped table-hover']);
    $rtTable->addRowHeaderColumns([
        _('ID'),
        _('Status'),
        _('Interval'),
        _('Name'),
        _('Last Job'),
        _('Actions'),
        _('Executor'),
    ]);
    
    foreach ($runtemplatesRaw as $runtemplateData) {
        $rtId = $runtemplateData['id'];
        
        // Get last job
        $lastJob = $jobber->listingQuery()
            ->select(['exitcode', 'id'], true)
            ->where(['runtemplate_id' => $rtId])
            ->orderBy('id DESC')
            ->limit(1)
            ->fetch();
        
        $row = [];
        
        // ID column
        $row[] = new ATag('runtemplate.php?id='.$rtId, '⚗️ #'.$rtId);
        
        // Status column (active/disabled with launch button)
        $row[] = $runtemplateData['active']
            ? new ATag('schedule.php?id='.$rtId.'&when=now&executor=Native', '▶️', ['title' => _('Launch now'), 'style' => 'font-size: 1.5em; color: green;'])
            : '<span style="font-size: 1.5em; color: lightgray;" title="'._('Disabled').'">🚧</span>';
        
        // Interval column
        $intervalEmoji = RunTemplate::getIntervalEmoji($runtemplateData['interv']);
        $intervalName = RunTemplate::codeToInterval($runtemplateData['interv']);
        $row[] = '<span title="'._($intervalName).'">'.$intervalEmoji.' '._($intervalName).'</span>';
        
        // Name column
        $row[] = new ATag('runtemplate.php?id='.$rtId, '<strong>'.$runtemplateData['name'].'</strong>');
        
        // Last job column
        if ($lastJob) {
            $row[] = new ATag('job.php?id='.$lastJob['id'], [
                '🏁 #'.$lastJob['id'].' ',
                new ExitCode($lastJob['exitcode'])
            ]);
        } else {
            $row[] = '<span style="color: #999; font-style: italic;">'._('No jobs yet').'</span>';
        }
        
        // Actions column
        $successIcons = RunTemplate::actionIcons(
            $runtemplateData['success'] ? unserialize($runtemplateData['success']) : null,
            ['style' => 'border-bottom: 4px solid green;']
        );
        $failIcons = RunTemplate::actionIcons(
            $runtemplateData['fail'] ? unserialize($runtemplateData['fail']) : null,
            ['style' => 'border-bottom: 4px solid red;']
        );
        $row[] = [
            new ATag('actions.php?id='.$rtId.'#SuccessActions', $successIcons),
            ' ',
            new ATag('actions.php?id='.$rtId.'#FailActions', $failIcons),
        ];
        
        // Executor column
        $row[] = new \MultiFlexi\Ui\ExecutorImage($runtemplateData['executor'], ['style' => 'height: 30px;']);
        
        $rtTable->addRowColumns($row);
    }
    
    $runtemplatesDiv->addItem($rtTable);
} else {
    $runtemplatesDiv->addItem(new \Ease\TWB4\Alert(
        'info',
        _('No RunTemplates configured yet. Click the button above to create one.')
    ));
}

// Last 10 jobs table
$runtemplatesDiv->addItem(new \Ease\Html\HrTag());
$runtemplatesDiv->addItem(new H3Tag('🏁 '._('Last 10 jobs')));

$jobs = $jobber->listingQuery()
    ->select(['job.id', 'begin', 'schedule', 'exitcode', 'launched_by', 'login', 'runtemplate_id', 'runtemplate.name AS runtemplate_name'], true)
    ->leftJoin('user ON user.id = job.launched_by')
    ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')
    ->where('job.company_id', $companer->getMyKey())
    ->where('job.app_id', $application->getMyKey())
    ->limit(10)
    ->orderBy('job.id DESC')
    ->fetchAll();

$jobList = new Table(null, ['class' => 'table table-sm table-hover']);
$jobList->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'), _('RunTemplate')]);

foreach ($jobs as $job) {
    $jobRow = [];
    
    // Job ID
    $jobRow[] = new ATag('job.php?id='.$job['id'], '🏁 '.$job['id']);
    
    // Launch time or scheduled time
    if (empty($job['begin'])) {
        if (!empty($job['schedule'])) {
            try {
                $scheduleTime = new \DateTime($job['schedule']);
                $relativeTime = \MultiFlexi\CompanyJobLister::getRelativeTime($scheduleTime);
                $jobRow[] = '💣 <span title="'.htmlspecialchars($job['schedule']).'">'.$relativeTime.'</span>';
            } catch (\Exception $e) {
                $jobRow[] = _('Scheduled');
            }
        } else {
            $jobRow[] = _('Not launched yet');
        }
    } else {
        $jobRow[] = [
            $job['begin'],
            ' ',
            new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))
        ];
    }
    
    // Exit code
    $jobRow[] = new ExitCode($job['exitcode']);
    
    // Launcher
    $jobRow[] = $job['launched_by'] 
        ? new ATag('user.php?id='.$job['launched_by'], $job['login']) 
        : _('Timer');
    
    // RunTemplate
    if (!empty($job['runtemplate_id'])) {
        $jobRow[] = new ATag('runtemplate.php?id='.$job['runtemplate_id'], $job['runtemplate_name'] ?? '#'.$job['runtemplate_id']);
    } else {
        $jobRow[] = '—';
    }
    
    $jobList->addRowColumns($jobRow);
}

$runtemplatesDiv->addItem($jobList);

// Job history link
$runtemplatesDiv->addItem(new LinkButton(
    'joblist.php?app_id='.$application->getMyKey().'&company_id='.$companer->getMyKey(),
    '🏁 '._('View Complete Job History'),
    'info btn-lg btn-block'
));

// Wrap everything in CompanyPanel with CompanyApplicationPanel
// This will show "Aktivní RunTemplates" panel with all RunTemplates for this app across companies
WebPage::singleton()->container->addItem(
    new CompanyPanel(
        $companer,
        new CompanyApplicationPanel($companyApp, $runtemplatesDiv)
    )
);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
