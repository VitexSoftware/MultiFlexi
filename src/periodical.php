<?php

/**
 * Multi Flexi - Periodical Tasks
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
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

$minutlyAppsRaw = \Ease\WebPage::getRequestValue('minutly');
$houryAppsRaw = \Ease\WebPage::getRequestValue('hourly');
$dailyAppsRaw = \Ease\WebPage::getRequestValue('daily');
$weeklyAppsRaw = \Ease\WebPage::getRequestValue('weekly');
$monthlyAppsRaw = \Ease\WebPage::getRequestValue('monthly');
$yearlyAppsRaw = \Ease\WebPage::getRequestValue('yearly');

// 3,4,6,5

function aIDs($apps)
{
    return empty($apps) ? [] : ((strchr($apps, ',') == false) ? [intval($apps) => intval($apps)] : array_combine(array_map('intval', explode(',', $apps)), array_map('intval', explode(',', $apps))));
}

$minutlyApps = aIDs($minutlyAppsRaw);
$hourlyApps = aIDs($houryAppsRaw);
$dailyApps = aIDs($dailyAppsRaw);
$weeklyApps = aIDs($weeklyAppsRaw);
$monthlyApps = aIDs($monthlyAppsRaw);
$yearlyApps = aIDs($yearlyAppsRaw);

$runTemplater = new \MultiFlexi\RunTemplate();
if (\Ease\WebPage::isPosted()) {
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $minutlyApps, 'i');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $hourlyApps, 'h');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $dailyApps, 'd');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $weeklyApps, 'w');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $monthlyApps, 'm');
    $runTemplater->assignAppsToCompany($companer->getMyKey(), $yearlyApps, 'y');
}

$appsByIntrv = $runTemplater->getCompanyAppsByInterval($companer->getMyKey());

$oPage->addItem(new PageTop(_('Periodical tasks')));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\H1Tag(_('Periodical tasks')));
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$periodSelectorsRow = new \Ease\TWB4\Row();

$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Minutely'), 'default', new CompanyAppIntervalSelector($companer, 'minutly', implode(',', array_keys($appsByIntrv['i'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Hourly'), 'default', new CompanyAppIntervalSelector($companer, 'hourly', implode(',', array_keys($appsByIntrv['h'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Daily'), 'default', new CompanyAppIntervalSelector($companer, 'daily', implode(',', array_keys($appsByIntrv['d'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Weekly'), 'default', new CompanyAppIntervalSelector($companer, 'weekly', implode(',', array_keys($appsByIntrv['w'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Monthly'), 'default', new CompanyAppIntervalSelector($companer, 'monthly', implode(',', array_keys($appsByIntrv['m'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Yearly'), 'default', new CompanyAppIntervalSelector($companer, 'yearly', implode(',', array_keys($appsByIntrv['y'])), 'periodbehaviour.php')));

//
//$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
//
//$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
//$chooseApp = new AppsSelector('appsassigned', implode(',', $assigned));

$addAppForm->addItem($periodSelectorsRow);
$addAppForm->addItem(new \Ease\Html\PTag());

$addAppForm->addItem(new \Ease\TWB4\SubmitButton(_('Apply'), 'success btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel($companer, $addAppForm));

//$apper = new \MultiFlexi\Application();
//foreach ($assigned as $assignedAppId) {
//    $apper->loadFromSQL($assignedAppId);
//    $oPage->container->addItem(new AppInfo($apper, $companer->getMyKey()));
//}

$oPage->addItem(new PageBottom());

$oPage->draw();
