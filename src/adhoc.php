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

WebPage::singleton()->onlyForLogged();

$appsAssigned = \Ease\WebPage::getRequestValue('appsassigned');
// 3,4,6,5

$companer = new \MultiFlexi\Company(\Ease\WebPage::getRequestValue('company_id', 'int'));

if (null === $companer->getMyKey()) {
    WebPage::singleton()->redirect('companies.php');
}

$companyApp = new \MultiFlexi\CompanyApp($companer);

WebPage::singleton()->addItem(new PageTop(_('Applications used by Company')));

$addAppForm = new \Ease\TWB5\Form();
$addAppForm->addItem(new \Ease\Html\InputHiddenTag('company_id', $companer->getMyKey()));

$assignedRaw = $companyApp->getAssigned()->fetchAll('app_id');
$assigned = empty($assignedRaw) ? [] : array_keys($assignedRaw);
WebPage::singleton()->container->addItem($addAppForm);

$apper = new \MultiFlexi\Application();

$launchTabs = new \Ease\TWB5\Tabs();

foreach ($assigned as $assignedAppId) {
    $apper->loadFromSQL($assignedAppId);
    $launchTabs->addTab(new AppLogo($apper, ['style' => 'height: 20px']).'&nbsp;'._($apper->getRecordName()), new AppInfo($apper, $companer->getMyKey()));
}

WebPage::singleton()->container->addItem(new CompanyPanel($companer, [new \Ease\Html\H2Tag(_('Application Launcher')), $launchTabs]));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
