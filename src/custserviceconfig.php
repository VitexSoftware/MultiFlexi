<?php

namespace MultiFlexi\Ui;

use MultiFlexi\Configuration;
use MultiFlexi\Ui\CustomAppConfigForm;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;
use MultiFlexi\Ui\WebPage;

/**
 * Multi Flexi - Config fields editor.
 *
 * @deprecated since version 1.14
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('App custom config Fields')));
$appId = WebPage::getRequestValue('app_id', 'int');
$companyId = WebPage::getRequestValue('company_id', 'int');

$configurator = new Configuration(['app_id' => $appId, 'company_id' => $companyId], ['autoload' => false]);
$configurator->setDataValue('app_id', $appId);

if ($oPage->isPosted()) {
    if ($configurator->takeData($_POST) && !is_null($configurator->saveToSQL())) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(
            _('Error saving Config fields'),
            'error'
        );
    }
}

$app = new \MultiFlexi\Application($appId);
$company = new \MultiFlexi\Company($companyId);
$runTemplater = new \MultiFlexi\RunTemplate();
$runTemplater->loadFromSQL($runTemplater->runTemplateID($appId, $companyId));

$appPanel = new ApplicationPanel($app, new CustomAppConfigForm($configurator));

$runTemplateButton = new \Ease\TWB4\LinkButton('runtemplate.php?id=' . $runTemplater->getMyKey(), '⚗️&nbsp;' . _('Run Template'), 'dark btn-lg btn-block');
$appPanel->headRow->addColumn(2, $runTemplateButton);

$oPage->container->addItem(new CompanyPanel($company, $appPanel));

$oPage->addItem(new PageBottom());

$oPage->draw();
