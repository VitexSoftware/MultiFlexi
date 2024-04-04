<?php

/**
 * Multi Flexi - Run Template page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\WebPage;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\RunTemplate;

require_once './init.php';
$oPage->onlyForLogged();


$runTemplate = new RunTemplate(WebPage::getRequestValue('id', 'int'));

if (WebPage::getRequestValue('clone')) {
    // TODO: Implement template cloning
}

$oPage->addItem(new PageTop(_('Company Tasks')));
$companies = new Company($runTemplate->getDataValue('company_id'));
$app = new Application($runTemplate->getDataValue('app_id'));
$app->setDataValue('company_id', $companies->getMyKey());
$app->setDataValue('app_id', $app->getMyKey());
$app->setDataValue('app_name', $app->getRecordName());

$appPanel = new ApplicationPanel($app, new AppRow($app->getData()));
$appPanel->headRow->addColumn(2, new LinkButton('periodbehaviour.php?app=' . $app->getMyKey() . '&company=' . $companies->getMyKey(), '🛠️&nbsp;' . _('Job Actions'), 'secondary btn-lg btn-block'));


$oPage->container->addItem(new CompanyPanel($companies, $appPanel));

new ServicesForCompanyForm($companies, ['id' => 'apptoggle']);

$oPage->addItem(new LinkButton('?clone=true&id=' . $runTemplate->getMyKey(), '🐾&nbsp;' . _('Clone'), 'info btn-sm  btn-block'));

$oPage->addItem(new PageBottom());
$oPage->draw();
