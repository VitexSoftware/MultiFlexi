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

$appsAssigned = \Ease\WebPage::getRequestValue('appsassigned');
// 3,4,6,5

$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (null === $companer->getMyKey()) {
    $oPage->redirect('companys.php');
}

$companyApp = new \MultiFlexi\CompanyApp($companer);

if (\Ease\WebPage::isPosted()) {
    $companyApp->assignApps(strstr($appsAssigned, ',') === false ? [(int) $appsAssigned] : array_map('intval', explode(',', $appsAssigned)));
}

$oPage->addItem(new PageTop(_('Applications used by Company')));

$addAppForm = new \Ease\TWB4\Form();
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
$chooseApp = new AppsSelector('appsassigned', implode(',', $assigned));

$addAppForm->addItem(new \Ease\Html\H2Tag(sprintf(_('Choose Applications to use with %s company'), $companer->getRecordName())));

$addAppForm->addItem($chooseApp);

$addAppForm->addItem(new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'success btn-lg btn-block'));

$oPage->container->addItem(new CompanyPanel($companer, $addAppForm));

// $apper = new \MultiFlexi\Application();
//
// $launchTabs = new \Ease\TWB4\Tabs();
// foreach ($assigned as $assignedAppId) {
//    $apper->loadFromSQL($assignedAppId);
//    $launchTabs->addTab($apper->getRecordName(), new AppInfo($apper, $companer->getMyKey()));
// }
// $oPage->container->addItem($launchTabs);

$oPage->addItem(new PageBottom());

$oPage->draw();
