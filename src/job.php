<?php

/**
 * Multi Flexi - Job Run archive.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Archived Job Run')));

$jobID = $oPage->getRequestValue('id', 'int');

$jobber = new \AbraFlexi\MultiFlexi\Job($jobID);

$appCompany = new \AbraFlexi\MultiFlexi\AppToCompany();
$appCompany->setMyKey($appCompany->appCompanyID($jobber->getDataValue('app_id'), $jobber->getDataValue('company_id')));

$appInfo = $appCompany->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$instanceRow = new Row();
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(empty($appInfo['image']) ? 'images/apps.svg' : $appInfo['image'], 'Logo', ['class' => 'img-fluid', 'style' => 'height: 64px']));
$instanceRow->addColumn(8, new \Ease\Html\H1Tag($instanceName));

$oPage->container->addItem(
        new Panel([_('App Run'), $instanceRow], 'info', new \Ease\Html\DivTag((new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert(nl2br($jobber->getDataValue('stdout'))), ['style' => 'font-family: monospace; background-color: black;'])
                , $jobber->getDataValue('stderr'))
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
