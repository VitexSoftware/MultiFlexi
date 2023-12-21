<?php

/**
 * Multi Flexi - AdHoc Job launcher
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$appsAssigned = \Ease\WebPage::getRequestValue('appsassigned');
// 3,4,6,5


$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (is_null($companer->getMyKey())) {
    $oPage->redirect('companys.php');
}

$companyApp = new \MultiFlexi\CompanyApp($companer);

$oPage->addItem(new PageTop(_('Applications used by Company')));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
$oPage->container->addItem($addAppForm);

$apper = new \MultiFlexi\Application();

$launchTabs = new \Ease\TWB4\Tabs();
foreach ($assigned as $assignedAppId) {
    $apper->loadFromSQL($assignedAppId);
    $launchTabs->addTab($apper->getRecordName(), new AppInfo($apper, $companer->getMyKey()));
}
$oPage->container->addItem(new CompanyPanel($companer, [new \Ease\Html\H2Tag(_('Application Launcher')),$launchTabs]));

$oPage->addItem(new PageBottom());

$oPage->draw();
