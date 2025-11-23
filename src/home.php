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

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$currentUser = \Ease\Shared::user();
$currentUserId = $currentUser->getUserID();

WebPage::singleton()->addItem(new PageTop(_('Home')));

$container = WebPage::singleton()->container;

// Welcome section
$welcomeCard = new \Ease\TWB4\Card();
$welcomeCard->addItem(new \Ease\Html\H2Tag(_('Welcome back').' '.$currentUser->getUserName()));
$welcomeCard->addItem(new \Ease\Html\PTag(_('This is your personal dashboard with overview of your recent activities.')));

// Quick actions
$actionRow = new \Ease\TWB4\Row();
$actionRow->addColumn(3, [
    new \Ease\TWB4\LinkButton('profile.php', new \Ease\TWB4\Widgets\FaIcon('user').' '._('Edit Profile'), 'primary btn-block'),
]);
$actionRow->addColumn(3, [
    new \Ease\TWB4\LinkButton('data-export-page.php', new \Ease\TWB4\Widgets\FaIcon('download').' '._('Export My Data'), 'info btn-block'),
]);
$actionRow->addColumn(3, [
    new \Ease\TWB4\LinkButton('consent-preferences.php', new \Ease\TWB4\Widgets\FaIcon('user-shield').' '._('Privacy Settings'), 'secondary btn-block'),
]);
$actionRow->addColumn(3, [
    new \Ease\TWB4\LinkButton('joblist.php', new \Ease\TWB4\Widgets\FaIcon('list').' '._('All Jobs'), 'success btn-block'),
]);

$welcomeCard->addItem($actionRow);
$container->addItem($welcomeCard);

// User statistics
$statsRow = new \Ease\TWB4\Row();

// Note: job table uses 'launched_by' text field, not user_id
// We'll show all jobs since we can't filter by current user
$jobEngine = new \MultiFlexi\Job();
$totalJobsCount = $jobEngine->getFluentPDO()->from($jobEngine->getMyTable())
    ->count();

// Count successful jobs
$successfulJobsCount = $jobEngine->getFluentPDO()->from($jobEngine->getMyTable())
    ->where('exitcode', 0)
    ->count();

// Count failed jobs
$failedJobsCount = $jobEngine->getFluentPDO()->from($jobEngine->getMyTable())
    ->where('exitcode IS NOT NULL AND exitcode <> 0')
    ->count();

// Count user's log entries
$logEngine = new \MultiFlexi\Logger();
$totalLogsCount = $logEngine->getFluentPDO()->from('log')
    ->where('log.user_id', $currentUserId)
    ->count();

// Display statistics cards (showing system-wide stats)
$totalJobsCard = new \Ease\TWB4\Card();
$totalJobsCard->addItem(new \Ease\Html\H3Tag($totalJobsCount, ['class' => 'text-center']));
$totalJobsCard->addItem(new \Ease\Html\PTag(_('Total Jobs in System'), ['class' => 'text-center text-muted']));
$statsRow->addColumn(3, $totalJobsCard);

$successJobsCard = new \Ease\TWB4\Card();
$successJobsCard->addItem(new \Ease\Html\H3Tag($successfulJobsCount, ['class' => 'text-center text-success']));
$successJobsCard->addItem(new \Ease\Html\PTag(_('Successful Jobs'), ['class' => 'text-center text-muted']));
$statsRow->addColumn(3, $successJobsCard);

$failedJobsCard = new \Ease\TWB4\Card();
$failedJobsCard->addItem(new \Ease\Html\H3Tag($failedJobsCount, ['class' => 'text-center text-danger']));
$failedJobsCard->addItem(new \Ease\Html\PTag(_('Failed Jobs'), ['class' => 'text-center text-muted']));
$statsRow->addColumn(3, $failedJobsCard);

$logsCard = new \Ease\TWB4\Card();
$logsCard->addItem(new \Ease\Html\H3Tag($totalLogsCount, ['class' => 'text-center']));
$logsCard->addItem(new \Ease\Html\PTag(_('Log Entries'), ['class' => 'text-center text-muted']));
$statsRow->addColumn(3, $logsCard);

$container->addItem($statsRow);

// Recent jobs section
$recentJobsCard = new \Ease\TWB4\Card(_('Recent Jobs'));

// Note: job table uses 'launched_by' text field, showing all recent jobs
$recentJobs = $jobEngine->getFluentPDO()->from('job j')
    ->select('j.id')
    ->select('a.name as app')
    ->select('c.name as company')
    ->select('j.begin')
    ->select('j.exitcode')
    ->select('j.schedule as launched')
    ->leftJoin('apps a ON j.app_id = a.id')
    ->leftJoin('company c ON j.company_id = c.id')
    ->orderBy('j.schedule DESC')
    ->limit(10)
    ->fetchAll() ?: [];

if (!empty($recentJobs)) {
    $jobsTable = new \Ease\Html\TableTag(null, ['class' => 'table table-sm table-striped']);
    $jobsTable->addRowHeaderColumns([
        _('ID'),
        _('Application'),
        _('Company'),
        _('Started'),
        _('Launched'),
        _('Status'),
    ]);

    foreach ($recentJobs as $job) {
        $statusBadge = '';

        if ($job['exitcode'] === null && $job['begin'] !== null) {
            $statusBadge = new \Ease\TWB4\Badge('🏃 '._('Running'), 'primary');
        } elseif ($job['exitcode'] === 0 || $job['exitcode'] === '0') {
            $statusBadge = new \Ease\TWB4\Badge('✅ '._('Success'), 'success');
        } elseif ($job['exitcode'] !== null) {
            $statusBadge = new \Ease\TWB4\Badge('❌ '._('Failed'), 'danger');
        } else {
            $statusBadge = new \Ease\TWB4\Badge('⏳ '._('Pending'), 'warning');
        }

        $jobsTable->addRowColumns([
            new \Ease\Html\ATag('job.php?id='.$job['id'], (string) $job['id']),
            (string) $job['app'],
            (string) $job['company'],
            $job['begin'] ? date('Y-m-d H:i:s', strtotime($job['begin'])) : '-',
            $job['launched'] ? date('Y-m-d H:i:s', strtotime($job['launched'])) : '-',
            $statusBadge,
        ]);
    }

    $recentJobsCard->addItem($jobsTable);
} else {
    $recentJobsCard->addItem(new \Ease\TWB4\Alert(_('No jobs found'), 'info'));
}

$container->addItem($recentJobsCard);

// Recent logs section
$recentLogsCard = new \Ease\TWB4\Card(_('My Recent Activity Log'));

// Create custom logger instance filtered by user_id
$userLogEngine = new \MultiFlexi\Logger();
$userLogEngine->filter = ['user_id' => $currentUserId];

// Add DataTable with user's logs
WebPage::singleton()->includeJavascript('js/dismisLog.js');
$recentLogsCard->addItem(new DBDataTable($userLogEngine, ['buttons' => false]));

$container->addItem($recentLogsCard);

// Account information section
$accountCard = new \Ease\TWB4\Card(_('Account Information'));

$accountInfo = new \Ease\Html\DlTag(null, ['class' => 'row']);

$accountInfo->addItem(new \Ease\Html\DtTag(_('Login'), ['class' => 'col-sm-3']));
$accountInfo->addItem(new \Ease\Html\DdTag($currentUser->getDataValue('login'), ['class' => 'col-sm-9']));

$accountInfo->addItem(new \Ease\Html\DtTag(_('Email'), ['class' => 'col-sm-3']));
$accountInfo->addItem(new \Ease\Html\DdTag($currentUser->getDataValue('email') ?: _('(not set)'), ['class' => 'col-sm-9']));

$accountInfo->addItem(new \Ease\Html\DtTag(_('Member since'), ['class' => 'col-sm-3']));
$accountInfo->addItem(new \Ease\Html\DdTag(
    date('F j, Y', strtotime($currentUser->getDataValue($currentUser->createColumn))),
    ['class' => 'col-sm-9'],
));

$accountCard->addItem($accountInfo);
$accountCard->addItem(new \Ease\Html\DivTag(
    new \Ease\TWB4\LinkButton('profile.php', new \Ease\TWB4\Widgets\FaIcon('edit').' '._('Edit Profile'), 'primary'),
    ['class' => 'text-right mt-3'],
));

$container->addItem($accountCard);

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
