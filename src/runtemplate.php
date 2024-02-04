<?php

/**
 * Multi Flexi - Run Template page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Document;
use Ease\Html\ImgTag;
use Ease\Html\SpanTag;
use Ease\TWB4\Panel;
use MultiFlexi\Company;

require_once './init.php';
$oPage->onlyForLogged();


$runTemplate = new \MultiFlexi\RunTemplate(\Ease\WebPage::getRequestValue('id', 'int'));

if (\Ease\WebPage::getRequestValue('clone')) {
}

$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company($runTemplate->getDataValue('company_id'));
$app = new \MultiFlexi\Application($runTemplate->getDataValue('app_id'));
$app->setDataValue('company_id', $companies->getMyKey());
$app->setDataValue('app_id', $app->getMyKey());
$app->setDataValue('app_name', $app->getRecordName());

$appPanel = new ApplicationPanel($app, new AppRow($app->getData()));
$appPanel->headRow->addColumn(2, new \Ease\TWB4\LinkButton('periodbehaviour.php?app=' . $app->getMyKey() . '&company=' . $companies->getMyKey(), '🛠️&nbsp;' . _('Job behaviour'), 'secondary btn-lg btn-block'));


$oPage->container->addItem(new CompanyPanel($companies, $appPanel));

new \MultiFlexi\Ui\ServicesForCompanyForm($companies, ['id' => 'apptoggle']);

$oPage->addItem(new \Ease\TWB4\LinkButton('?clone=true&id=' . $runTemplate->getMyKey(), '🐾&nbsp;' . _('Clone'), 'info btn-sm  btn-block'));

$oPage->addItem(new PageBottom());
$oPage->draw();
