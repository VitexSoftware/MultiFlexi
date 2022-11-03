<?php

/**
 * Multi Flexi - Customer instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2017-2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;
use AbraFlexi\MultiFlexi\Ui\RegisterAppForm;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Application')));

$appCompany = new \AbraFlexi\MultiFlexi\AppToCompany($oPage->getRequestValue('id', 'int'));
$appInfo = $appCompany->getAppInfo();
$apps = new Application($appInfo['app_id']);
$instanceName = $apps->getDataValue('nazev');

if ($oPage->isPosted()) {
    if ($apps->takeData($_POST) && !is_null($apps->saveToSQL())) {
        $apps->addStatusMessage(_('Application Saved'), 'success');
//        $apps->prepareRemoteAbraFlexi();
        $oPage->redirect('?id=' . $apps->getMyKey());
    } else {
        $apps->addStatusMessage(_('Error saving Application'), 'error');
    }
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new AppInfo($apps, \Ease\WebPage::getRequestValue('company')));

$instanceRow->addColumn(4, new \Ease\Html\ImgTag(empty($apps->getDataValue('image')) ? 'images/apps.svg' : $apps->getDataValue('image'), 'Logo', ['class' => 'img-fluid']));

$oPage->container->addItem(new Panel($instanceName, 'info', $instanceRow, new \Ease\Html\DivTag(new \Ease\Html\IframeTag('run.php?id=' . \Ease\WebPage::getRequestValue('id'), ['id' => 'shell']), ['class' => 'iframe-container'])));

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
