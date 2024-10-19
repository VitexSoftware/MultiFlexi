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
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Archived Job Run')));
$jobID = $oPage->getRequestValue('id', 'int');
$jobber = new \MultiFlexi\Job($jobID);
$runTemplate = new \MultiFlexi\RunTemplate($jobber->getDataValue('runtemplate_id'));

if (null === $runTemplate->getMyKey()) { // TODO: Remove
    $runTemplate->setMyKey($runTemplate->runTemplateID($jobber->getDataValue('app_id'), $jobber->getDataValue('company_id')));
}

$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$errorTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $jobber->getDataValue('stderr')))), ['style' => 'background: #8B0000; font-family: monospace;']);
$stdTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert((string) $jobber->getDataValue('stdout')))), ['style' => 'background: #000000; font-family: monospace;']);

$outputTabs = new \Ease\TWB4\Tabs();
$outputTabs->addTab(_('Output'), [$stdTerminal, \strlen($jobber->getOutput()) ? new \Ease\TWB4\LinkButton('joboutput.php?id='.$jobID.'&mode=std', _('Download'), 'secondary btn-block') : _('No output')]);
$outputTabs->addTab(_('Errors'), [$errorTerminal, \strlen($jobber->getErrorOutput()) ? new \Ease\TWB4\LinkButton('joboutput.php?id='.$jobID.'&mode=err', _('Download'), 'secondary btn-block') : _('No errors')], empty($jobber->getOutput()));

$runTemplateButton = new \Ease\TWB4\LinkButton('runtemplate.php?id='.$runTemplate->getMyKey(), '⚗️&nbsp;'._('Run Template'), 'dark btn-lg btn-block');

$appPanel = new ApplicationPanel($apps, $outputTabs, new JobInfo($jobber));
$appPanel->headRow->addColumn(2, $runTemplateButton);

// $relaunchButton = new \Ease\TWB4\LinkButton('launch.php?id='.$runTemplate->getMyKey().'&app_id='.$appInfo['app_id'].'&company_id='.$appInfo['company_id'], '&lt;'._('Relaunch').'💨', 'success btn-lg btn-block');

$scheduleButton = new \Ease\TWB4\LinkButton('schedule.php?id='.$runTemplate->getMyKey().'&app_id='.$appInfo['app_id'].'&company_id='.$appInfo['company_id'], [_('Schedule').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/launchinbackground.svg', _('Launch'), ['height' => '30px'])], 'primary btn-lg');

$previousJobId = $jobber->getPreviousJobId(true, true, true);

if ($previousJobId) {
    $previousButton = new \Ease\TWB4\LinkButton('job.php?id='.$previousJobId, '◀️ '._('Previous'), 'info btn-lg btn-block');
} else {
    $previousButton = new \Ease\TWB4\LinkButton('#', '◀️ '._('Previous'), 'info btn-lg btn-block disabled');
}

$nextJobId = $jobber->getNextJobId(true, true, true);

if ($nextJobId) {
    $nextButton = new \Ease\TWB4\LinkButton('job.php?id='.$nextJobId, _('Next').' ▶️️', 'info btn-lg btn-block');
} else {
    $nextButton = new \Ease\TWB4\LinkButton('#', _('Next').' ▶️️', 'info btn-lg btn-block disabled');
}

$appPanel->headRow->addColumn(2, $previousButton);
$appPanel->headRow->addColumn(2, $scheduleButton);
$appPanel->headRow->addColumn(2, $nextButton);

$oPage->container->addItem(
    new CompanyPanel(new \MultiFlexi\Company($appInfo['company_id']), $appPanel),
);

$oPage->addItem(new PageBottom());
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
$oPage->draw();
