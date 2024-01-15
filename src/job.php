<?php

/**
 * Multi Flexi - Job Run Overview.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Application;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Archived Job Run')));
$jobID = $oPage->getRequestValue('id', 'int');
$jobber = new \MultiFlexi\Job($jobID);
$runTemplate = new \MultiFlexi\RunTemplate();
$runTemplate->setMyKey($runTemplate->runTemplateID($jobber->getDataValue('app_id'), $jobber->getDataValue('company_id')));
$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$errorTerminal = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(strval($jobber->getDataValue('stderr'))))), ['style' => 'background: #8B0000; font-family: monospace;']);
$stdTerminal   = new \Ease\Html\DivTag(nl2br(str_replace('background-color: black; ', '', (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(strval($jobber->getDataValue('stdout'))))), ['style' => 'background: #000000; font-family: monospace;']);

$outputTabs = new \Ease\TWB4\Tabs();
$outputTabs->addTab(_('Output'), [$stdTerminal, new \Ease\TWB4\LinkButton('joboutput.php?id=' . $jobID . '&mode=std', _('Download'), 'secondary btn-block') ]);
$outputTabs->addTab(_('Errors'), [$errorTerminal, new \Ease\TWB4\LinkButton('joboutput.php?id=' . $jobID . '&mode=err', _('Download'), 'secondary btn-block') ], empty($jobber->getDataValue('stdout')));

$oPage->container->addItem(
    new CompanyPanel(new \MultiFlexi\Company($appInfo['company_id']), new ApplicationPanel($apps, $outputTabs, new JobInfo($jobber)))
);

$oPage->addItem(new PageBottom());
WebPage::singleton()->addCss('
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

');
$oPage->draw();
