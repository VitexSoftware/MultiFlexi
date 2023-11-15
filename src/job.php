<?php

/**
 * Multi Flexi - Job Run Overview.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Panel;
use Ease\TWB4\Row;
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

$errorTerminal = new \Ease\ui\OldTerminal((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(strval($jobber->getDataValue('stderr'))));
$errorTerminal->green = 0;
$errorTerminal->red = 150;

$oPage->container->addItem(
    new Panel(
        new JobInfo($jobber),
        'default',
        new \Ease\ui\OldTerminal((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(strval($jobber->getDataValue('stdout')))),
        $errorTerminal,
        new JobInfo($jobber)
    )
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
