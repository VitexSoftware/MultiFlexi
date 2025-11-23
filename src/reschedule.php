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

$jobID = WebPage::singleton()->getRequestValue('job_id', 'int');
$jobber = new \MultiFlexi\Job($jobID);

if (!$jobber->getMyKey()) {
    WebPage::singleton()->addStatusMessage(_('Job not found'), 'error');
    WebPage::singleton()->redirect('main.php');
}

$runTemplate = new \MultiFlexi\RunTemplate($jobber->getDataValue('runtemplate_id'));
$appInfo = $runTemplate->getAppInfo();

WebPage::singleton()->addItem(new PageTop(_('Re-schedule Orphaned Job')));

// Check if job has already started
if ($jobber->getDataValue('begin')) {
    WebPage::singleton()->container->addItem(new \Ease\TWB4\Alert('warning', _('This job has already been executed and cannot be re-scheduled.')));
    WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('job.php?id='.$jobID, _('Back to Job'), 'info'));
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

// Check if job is already scheduled
if ($jobber->isScheduled()) {
    WebPage::singleton()->container->addItem(new \Ease\TWB4\Alert('info', _('This job is already scheduled in the queue.')));
    $scheduleInfo = $jobber->scheduledJobInfo();

    if (!empty($scheduleInfo)) {
        $scheduleData = $scheduleInfo[0];
        $scheduledTime = new \DateTime($scheduleData['after'] ?? $scheduleData['scheduled']);
        WebPage::singleton()->container->addItem(new \Ease\Html\DivTag(_('Scheduled for: ').$scheduledTime->format('Y-m-d H:i:s')));
    }

    WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('job.php?id='.$jobID, _('Back to Job'), 'info'));
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();

    exit;
}

// Job is orphaned - allow re-scheduling
$when = WebPage::getRequestValue('when');

if (WebPage::isPosted() || $when === 'now') {
    $scheduledTime = $when ? new \DateTime($when) : new \DateTime();

    try {
        // Re-schedule the job by creating a new schedule entry
        $scheduleId = $jobber->scheduleJobRun($scheduledTime);

        WebPage::singleton()->addStatusMessage(
            sprintf(_('Job #%d has been re-scheduled for %s'), $jobID, $scheduledTime->format('Y-m-d H:i:s')),
            'success',
        );

        WebPage::singleton()->container->addItem(new \Ease\TWB4\Alert('success', [
            new \Ease\Html\H3Tag(_('Job Successfully Re-scheduled')),
            new \Ease\Html\PTag(sprintf(_('Job #%d will be executed at %s'), $jobID, $scheduledTime->format('Y-m-d H:i:s'))),
            new \Ease\Html\PTag(sprintf(_('Schedule ID: %d'), $scheduleId)),
        ]));

        WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('job.php?id='.$jobID, _('Back to Job'), 'info btn-lg'));
        WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('queue.php', _('View Queue'), 'primary btn-lg'));
    } catch (\Exception $e) {
        WebPage::singleton()->addStatusMessage(
            sprintf(_('Failed to re-schedule job: %s'), $e->getMessage()),
            'error',
        );

        WebPage::singleton()->container->addItem(new \Ease\TWB4\Alert('danger', [
            new \Ease\Html\H3Tag(_('Re-scheduling Failed')),
            new \Ease\Html\PTag($e->getMessage()),
        ]));
    }
} else {
    // Show re-schedule form
    $panel = new \Ease\TWB4\Panel(_('Re-schedule Orphaned Job'), 'warning');

    $panel->addItem(new \Ease\TWB4\Alert('warning', [
        '⚠️ ',
        _('This job has not been executed yet and does not have its place in the execution queue. This can happen when the schedule queue is manually cleared or due to system errors.'),
    ]));

    $infoDiv = new \Ease\Html\DivTag();
    $infoDiv->addItem(new \Ease\Html\DivTag([new \Ease\Html\StrongTag(_('Job ID: ')), $jobID]));
    $infoDiv->addItem(new \Ease\Html\DivTag([new \Ease\Html\StrongTag(_('Application: ')), $appInfo['app_name']]));
    $infoDiv->addItem(new \Ease\Html\DivTag([new \Ease\Html\StrongTag(_('RunTemplate: ')), $runTemplate->getRecordName()]));
    $infoDiv->addItem(new \Ease\Html\DivTag([new \Ease\Html\StrongTag(_('Original Schedule: ')), $jobber->getDataValue('schedule')]));
    $panel->addItem($infoDiv);

    $form = new \Ease\TWB4\Form(['action' => 'reschedule.php']);
    $form->addItem(new \Ease\Html\InputHiddenTag('job_id', $jobID));

    $form->addItem(new \Ease\TWB4\FormGroup(
        _('Schedule Time'),
        new \Ease\Html\InputDateTimeTag('when', date('Y-m-d H:i', strtotime('+5 minutes')), ['class' => 'form-control']),
    ));

    $buttonRow = new \Ease\TWB4\Row();
    $buttonRow->addColumn(6, new \Ease\TWB4\SubmitButton('⏰ '._('Re-schedule Now'), 'primary btn-block btn-lg'));
    $buttonRow->addColumn(6, new \Ease\TWB4\LinkButton('job.php?id='.$jobID, _('Cancel'), 'secondary btn-block btn-lg'));
    $form->addItem($buttonRow);

    $panel->addItem($form);

    $quickButtons = new \Ease\TWB4\Row();
    $quickButtons->addColumn(4, new \Ease\TWB4\LinkButton('reschedule.php?job_id='.$jobID.'&when=now', '⚡ '._('Schedule Immediately'), 'success btn-block'));
    $quickButtons->addColumn(4, new \Ease\TWB4\LinkButton('reschedule.php?job_id='.$jobID.'&when='.date('Y-m-d H:i:s', strtotime('+1 hour')), '🕐 '._('Schedule in 1 Hour'), 'info btn-block'));
    $quickButtons->addColumn(4, new \Ease\TWB4\LinkButton('reschedule.php?job_id='.$jobID.'&when='.date('Y-m-d H:i:s', strtotime('+1 day')), '📅 '._('Schedule Tomorrow'), 'info btn-block'));

    WebPage::singleton()->container->addItem(new CompanyPanel(
        new \MultiFlexi\Company($appInfo['company_id']),
        [$panel, $quickButtons],
    ));
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
