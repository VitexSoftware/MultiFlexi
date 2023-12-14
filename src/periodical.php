<?php

/**
 * Multi Flexi - Periodical Tasks
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (is_null($companer->getMyKey())) {
    $oPage->redirect('companys.php');
}

$_SESSION['company'] = $companer->getMyKey();

$houryAppsRaw = \Ease\WebPage::getRequestValue('hourly');
$dailyAppsRaw = \Ease\WebPage::getRequestValue('daily');
$weeklyAppsRaw = \Ease\WebPage::getRequestValue('weekly');
$monthlyAppsRaw = \Ease\WebPage::getRequestValue('monthly');
$yearlyAppsRaw = \Ease\WebPage::getRequestValue('yearly');

// 3,4,6,5

function aIDs($apps) {
    return empty($apps) ? [] : ((strchr($apps, ',') == false) ? [intval($apps) => intval($apps)] : array_combine(array_map('intval', explode(',', $apps)), array_map('intval', explode(',', $apps))));
}

$houryApps = aIDs($houryAppsRaw);
$dailyApps = aIDs($dailyAppsRaw);
$weeklyApps = aIDs($weeklyAppsRaw);
$monthlyApps = aIDs($monthlyAppsRaw);
$yearlyApps = aIDs($yearlyAppsRaw);

$runTemplater = new \MultiFlexi\RunTemplate();
if (\Ease\WebPage::isPosted()) {
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $houryApps, 'h');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $dailyApps, 'd');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $weeklyApps, 'w');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $monthlyApps, 'm');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $yearlyApps, 'y');
}

$appsByIntrv = $runTemplater->getCompanyAppsByInterval($companer->getMyKey());

$oPage->addItem(new PageTop(_('Periodical Tasks')));

$oPage->container->addItem(new \Ease\Html\H1Tag(sprintf(_('Periodical tasks for %s company'), $companer->getRecordName())));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$periodSelectorsRow = new \Ease\TWB4\Row();

$periodSelectorsRow->addColumn(2, new CompanyLogo($companer));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Hourly'), 'default', new AppsSelector('hourly', implode(',', array_keys($appsByIntrv['h'])))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Daily'), 'default', new AppsSelector('daily', implode(',', array_keys($appsByIntrv['d'])))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Weekly'), 'default', new AppsSelector('weekly', implode(',', array_keys($appsByIntrv['w'])))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Monthly'), 'default', new AppsSelector('monthly', implode(',', array_keys($appsByIntrv['m'])))));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Yearly'), 'default', new AppsSelector('yearly', implode(',', array_keys($appsByIntrv['y'])))));

//
//$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
//
//$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
//$chooseApp = new AppsSelector('appsassigned', implode(',', $assigned));

$addAppForm->addItem($periodSelectorsRow);
$addAppForm->addItem(new \Ease\Html\PTag());

$addAppForm->addItem(new \Ease\TWB4\SubmitButton(_('(Un)Assign Applications'), 'success btn-lg btn-block'));

$oPage->container->addItem($addAppForm);

//$apper = new \MultiFlexi\Application();
//foreach ($assigned as $assignedAppId) {
//    $apper->loadFromSQL($assignedAppId);
//    $oPage->container->addItem(new AppInfo($apper, $companer->getMyKey()));
//}

$oPage->addItem(new PageBottom());

$oPage->draw();
