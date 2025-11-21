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

use MultiFlexi\Application;

require_once './init.php';
WebPage::singleton()->onlyForLogged();
WebPage::singleton()->addItem(new PageTop(_('Archived Job Run')));
$jobID = WebPage::singleton()->getRequestValue('id', 'int');
$jobber = new \MultiFlexi\Job($jobID);

if (!$jobber->getMyKey()) {
    WebPage::singleton()->addStatusMessage(_('Job not found'), 'error');
    WebPage::singleton()->redirect('main.php');
}

$runTemplate = new \MultiFlexi\RunTemplate($jobber->getDataValue('runtemplate_id'));

if (!$runTemplate->getMyKey()) {
    // RunTemplate was deleted - show limited job info
    WebPage::singleton()->addStatusMessage(_('Warning: RunTemplate for this job was deleted'), 'warning');
    
    WebPage::singleton()->addItem(new PageTop(_('Job').' #'.$jobID));
    
    $jobPanel = new \Ease\TWB4\Panel(_('Job Information'), 'warning');
    $jobPanel->addItem(new \Ease\TWB4\Alert('warning', [
        '⚠️ ',
        _('The RunTemplate associated with this job has been deleted. Job information is limited.'),
    ]));
    
    $infoDiv = new \Ease\Html\DivTag();
    $infoDiv->addItem(new \Ease\Html\StrongTag(_('Job ID: ')));
    $infoDiv->addItem($jobID);
    $infoDiv->addItem(new \Ease\Html\BRTag());
    $infoDiv->addItem(new \Ease\Html\StrongTag(_('RunTemplate ID: ')));
    $infoDiv->addItem($jobber->getDataValue('runtemplate_id').' '._('(deleted)'));
    $infoDiv->addItem(new \Ease\Html\BRTag());
    $infoDiv->addItem(new \Ease\Html\StrongTag(_('Status: ')));
    $infoDiv->addItem($jobber->getDataValue('exitcode') !== null ? _('Completed') : _('Pending'));
    
    $jobPanel->addItem($infoDiv);
    
    $outputTabs = new \Ease\TWB4\Tabs();
    $stdTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(stripslashes((string) $jobber->getDataValue('stdout'))))), ['style' => 'background: black; font-family: monospace;']);
    $errorTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(stripslashes((string) $jobber->getDataValue('stderr'))))), ['style' => 'background: #330000; font-family: monospace;']);
    
    $outputTabs->addTab(_('Output'), [$stdTerminal]);
    $outputTabs->addTab(_('Errors'), [$errorTerminal]);
    
    $jobPanel->addItem($outputTabs);
    
    WebPage::singleton()->container->addItem($jobPanel);
    WebPage::singleton()->container->addItem(new \Ease\TWB4\LinkButton('main.php', _('Back to Dashboard'), 'primary'));
    WebPage::singleton()->addItem(new PageBottom());
    WebPage::singleton()->draw();
    exit;
}

$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$errorTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(stripslashes((string) $jobber->getDataValue('stderr'))))), ['style' => 'background: #330000; font-family: monospace;']);
$stdTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(stripslashes((string) $jobber->getDataValue('stdout'))))), ['style' => 'background:  black; font-family: monospace;']);

$liveOutputSocket = \Ease\Shared::cfg('LIVE_OUTPUT_SOCKET');

if ($liveOutputSocket && isset($_SESSION['ws_token'])) {
    $wsToken = $_SESSION['ws_token'];

    WebPage::singleton()->addJavaScript(
        <<<EOD

var ws = new WebSocket('{$liveOutputSocket}?token={$wsToken}');
ws.onmessage = function(event) {
    var data = JSON.parse(event.data);
    var output = document.getElementById('live-output');
    output.textContent += data.message + '\\n';
};

EOD
    );
}

$outputTabs = new \Ease\TWB4\Tabs();
$outputTabs->addTab(_('Output').' '.(\strlen($jobber->getOutput()) ? ' <span class="badge badge-secondary">'.substr_count($jobber->getOutput(), "\n").'</span>' : '<span class="badge badge-invers">💭</span>'), [$stdTerminal, \strlen($jobber->getOutput()) ? new \Ease\TWB4\LinkButton('joboutput.php?id='.$jobID.'&mode=std', _('Download'), 'secondary btn-block') : _('No output'), new \Ease\Html\PreTag('', ['id' => 'live-output'])]);
$outputTabs->addTab(_('Errors').' '.(empty($jobber->getErrorOutput()) ? ' <span class="badge badge-success">0</span>' : '<span class="badge badge-warning">'.substr_count($jobber->getErrorOutput(), "\n").'</span>'), [$errorTerminal, \strlen($jobber->getErrorOutput()) ? new \Ease\TWB4\LinkButton('joboutput.php?id='.$jobID.'&mode=err', _('Download'), 'secondary btn-block') : _('No errors')], empty($jobber->getOutput()));

$artifactor = new \MultiFlexi\Artifact();
$artifacts = $artifactor->listingQuery()->where('job_id', $jobID);

if ($artifacts->count()) {
    WebPage::singleton()->includeJavaScript('js/highlight.min.js');
    WebPage::singleton()->includeCss('css/highlight-default.min.css');
    WebPage::singleton()->addJavaScript('hljs.highlightAll();');
    $artifactsDiv = new \Ease\Html\DivTag();

    foreach ($artifacts->fetchAll() as $artifactData) {
        switch ($artifactData['content_type']) {
            case 'application/json':
                $code = json_encode(json_decode($artifactData['artifact']), \JSON_PRETTY_PRINT | \JSON_UNESCAPED_UNICODE | \JSON_UNESCAPED_SLASHES | \JSON_UNESCAPED_LINE_TERMINATORS);

                break;

            default:
                $code = $artifactData['artifact'];

                break;
        }

        $artifactsDiv->addItem(new \Ease\TWB4\Panel([new \Ease\Html\ATag('getartifact.php?id='.$artifactData['id'], '💾', ['class' => 'btn btn-info btn-sm']), '&nbsp;'.$artifactData['filename']], 'inverse', new \Ease\Html\DivTag(new \Ease\Html\PreTag('<code>'.$code.'</code>'), ['style' => 'font-family: monospace; color: black']), $artifactData['note']));
    }

    $outputTabs->addTab(_('Artifacts').' <span class="badge badge-success">'.$artifacts->count().'</span>', $artifactsDiv);
}

$runTemplateButton = new RuntemplateButton($runTemplate);

// $relaunchButton = new \Ease\TWB4\LinkButton('launch.php?id='.$runTemplate->getMyKey().'&app_id='.$appInfo['app_id'].'&company_id='.$appInfo['company_id'], '&lt;'._('Relaunch').'💨', 'success btn-lg btn-block');

if ($jobber->getDataValue('begin')) {
    // Job already started/finished
    $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runTemplate->getMyKey().'&app_id='.$appInfo['app_id'].'&company_id='.$appInfo['company_id'], [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-block');
} else {
    // Job not started yet - check if scheduled
    if ($jobber->isScheduled()) {
        // Job is in schedule queue - allow cancellation
        $scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?cancel='.$jobber->getMyKey().'&templateid='.$runTemplate->getMyKey().'&app_id='.$jobber->getDataValue('app_id').'&company_id='.$runTemplate->getDataValue('company_id'), [_('Cancel').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/cancel.svg', _('Cancel').'&nbsp;&nbsp;', ['height' => '60px'])], 'warning btn-block');
    } else {
        // Orphaned job - no schedule entry, allow re-scheduling
        $scheduleButton = new \Ease\TWB4\LinkButton('reschedule.php?job_id='.$jobber->getMyKey(), ['⏰ '._('Re-schedule')], 'danger btn-block');
    }
}

$previousJobId = $jobber->getPreviousJobId(true, true, true);

if ($previousJobId) {
    $previousButton = new \Ease\TWB4\LinkButton('job.php?id='.$previousJobId, '◀️ '._('Previous').' 🏁', 'info btn-lg btn-block');
} else {
    $previousButton = new \Ease\TWB4\LinkButton('#', '◀️ '._('Previous').' 🏁', 'info btn-lg btn-block disabled');
}

$nextJobId = $jobber->getNextJobId(true, true, true);

if ($nextJobId) {
    $nextButton = new \Ease\TWB4\LinkButton('job.php?id='.$nextJobId, '🏁 '._('Next').' ▶️️', 'info btn-lg btn-block');
} else {
    $nextButton = new \Ease\TWB4\LinkButton('#', '🏁 '._('Next').' ▶️️', 'info btn-lg btn-block disabled');
}

$jobFoot = new \Ease\TWB4\Row();
$jobFoot->addColumn(2, $previousButton);
$jobFoot->addColumn(2, $nextButton);
$jobFoot->addColumn(2, $scheduleButton);
$jobFoot->addColumn(4, $runTemplateButton);

$appPanel = new ArchivedJobPanel($jobber, [new JobInfo($jobber), $outputTabs], $jobFoot);

WebPage::singleton()->container->addItem(
    new CompanyPanel(new \MultiFlexi\Company($appInfo['company_id']), $appPanel),
);

WebPage::singleton()->addItem(new PageBottom('job/'.$jobber->getMyKey()));
WebPage::singleton()->addCss(<<<'EOD'

.iframe-container {
  overflow: hidden;
  padding-top: 56.25%;
  position: relative;
}

.iframe-container iframe {
   border: 0;
   height: 100%;
   left: 0;
   position: absolute;
   top: 0;
   width: 100%;
}

/* 4x3 Aspect Ratio */
.iframe-container-4x3 {
  padding-top: 75%;
}


EOD);
WebPage::singleton()->draw();
