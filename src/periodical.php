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

require_once './init.php';

$oPage->onlyForLogged();

$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (null === $companer->getMyKey()) {
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
    return empty($apps) ? [] : ((strstr($apps, ',') === false) ? [(int) $apps => (int) $apps] : array_combine(array_map('intval', explode(',', $apps)), array_map('intval', explode(',', $apps))));
}

$minutlyApps = aIDs($minutlyAppsRaw);
$hourlyApps = aIDs($houryAppsRaw);
$dailyApps = aIDs($dailyAppsRaw);
$weeklyApps = aIDs($weeklyAppsRaw);
$monthlyApps = aIDs($monthlyAppsRaw);
$yearlyApps = aIDs($yearlyAppsRaw);

$runTemplater = new \MultiFlexi\RunTemplate();

if (\Ease\WebPage::isPosted()) {
    $runTemplater->setPeriods($companer->getMyKey(), $minutlyApps, 'i');
    $runTemplater->setPeriods($companer->getMyKey(), $hourlyApps, 'h');
    $runTemplater->setPeriods($companer->getMyKey(), $dailyApps, 'd');
    $runTemplater->setPeriods($companer->getMyKey(), $weeklyApps, 'w');
    $runTemplater->setPeriods($companer->getMyKey(), $monthlyApps, 'm');
    $runTemplater->setPeriods($companer->getMyKey(), $yearlyApps, 'y');
}

$appsByIntrv = $runTemplater->getCompanyRunTemplatesByInterval($companer->getMyKey());

$oPage->addItem(new PageTop(_('Periodical tasks')));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\H1Tag(_('Periodical tasks')));
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$periodSelectorsRow = new \Ease\TWB4\Row();

$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Minutely'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'minutly', implode(',', array_keys($appsByIntrv['i'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Hourly'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'hourly', implode(',', array_keys($appsByIntrv['h'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Daily'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'daily', implode(',', array_keys($appsByIntrv['d'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Weekly'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'weekly', implode(',', array_keys($appsByIntrv['w'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Monthly'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'monthly', implode(',', array_keys($appsByIntrv['m'])), 'periodbehaviour.php')));
$periodSelectorsRow->addColumn(2, new \Ease\TWB4\Panel(_('Yearly'), 'default', new CompanyRuntemplateIntervalSelector($companer, 'yearly', implode(',', array_keys($appsByIntrv['y'])), 'periodbehaviour.php')));

//
// $assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
//
// $assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
// $chooseApp = new AppsSelector('appsassigned', implode(',', $assigned));

$addAppForm->addItem($periodSelectorsRow);
$addAppForm->addItem(new \Ease\Html\PTag());

$addAppForm->addItem(new \Ease\TWB4\SubmitButton(_('🍏 Apply'), 'success btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel($companer, $addAppForm));

// $apper = new \MultiFlexi\Application();
// foreach ($assigned as $assignedAppId) {
//    $apper->loadFromSQL($assignedAppId);
//    $oPage->container->addItem(new AppInfo($apper, $companer->getMyKey()));
// }

$oPage->addItem(new PageBottom());

$oPage->draw();
