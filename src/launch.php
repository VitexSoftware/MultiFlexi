<?php

/**
 * Multi Flexi - AdHoc Job Run.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Application')));

$companyId = $oPage->getRequestValue('company_id','int');
$appId = $oPage->getRequestValue('app_id','int');

$runTemplate = new \AbraFlexi\MultiFlexi\RunTemplate($oPage->getRequestValue('id', 'int'));
if ($companyId && $appId) {
    if ($runTemplate->runTemplateID($appId, $companyId) == 0) {
        $runTemplate->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
    }
}

$appInfo = $runTemplate->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $appInfo['app_name'];

$instanceRow = new Row();
$instanceRow->addColumn(2, new \Ease\Html\ImgTag(empty($appInfo['image']) ? 'images/apps.svg' : $appInfo['image'], 'Logo', ['class' => 'img-fluid', 'style' => 'height: 64px']));
$instanceRow->addColumn(8, new \Ease\Html\H1Tag($instanceName));

$envTable = new EnvironmentView($runTemplate->getAppEnvironment());

$oPage->container->addItem(new Panel(_('App Run'), 'info', [$instanceRow, $envTable], new \Ease\Html\DivTag(new \Ease\Html\IframeTag('run.php?id=' . $runTemplate->getMyKey(), ['id' => 'shell', 'title' => $instanceName]), ['class' => 'iframe-container'])));

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
