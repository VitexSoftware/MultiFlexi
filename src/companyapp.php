<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\SpanTag;
use MultiFlexi\Application;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();

$companer = new Company(WebPage::getRequestValue('company_id', 'int'));
$application = new Application(WebPage::getRequestValue('app_id', 'int'));

$oPage->addItem(new PageTop(_($application->getRecordName()) . '@' . $companer->getRecordName()));
//$companyApp = new \MultiFlexi\RunTemplate(\Ease\Document::getRequestValue('id', 'int'));
//$appData = $companyApp->getAppInfo();
//$companies = new Company($companyApp->getDataValue('company_id'));
//if (strlen($companies->getDataValue('logo'))) {
//    $companyTasksHeading[] = new \Ease\Html\ImgTag($companies->getDataValue('logo'), 'logo', ['class' => 'img-fluid','style' => 'height']);
//}

$companyTasksHeading[] = new SpanTag($companer->getDataValue('name') . '&nbsp;', ['style' => 'font-size: xxx-large;']);
$companyTasksHeading[] = _('Assigned applications');

$runTemplater = new \MultiFlexi\RunTemplate();
$runtemplatesRaw = $runTemplater->listingQuery()->where('app_id',$application->getMyKey())->where('company_id',$companer->getMyKey());

$runtemplatesDiv = new \Ease\Html\DivTag();

foreach ($runtemplatesRaw as $runtemplateData) {
    $runtemplateRow = new \Ease\TWB4\Row();
    $runtemplateRow->addColumn(1, '#' . strval($runtemplateData['id']));
    $runtemplateRow->addColumn(4, $runtemplateData['name']);
    $runtemplatesDiv->addItem(new \Ease\Html\ATag('runtemplate.php?id=' . $runtemplateData['id'], $runtemplateRow));
}

$oPage->container->addItem(new CompanyPanel($companer, new ApplicationPanel($application, $runtemplatesDiv)));
$oPage->addItem(new PageBottom());
$oPage->draw();
