<?php

namespace AbraFlexi\MultiSetup\Ui;

use Ease\Html\H1Tag;
use AbraFlexi\MultiSetup\Configuration;
use AbraFlexi\MultiSetup\Ui\CustomAppConfigForm;
use AbraFlexi\MultiSetup\Ui\PageBottom;
use AbraFlexi\MultiSetup\Ui\PageTop;
use AbraFlexi\MultiSetup\Ui\WebPage;

/**
 * Multi Flexi - Config fields editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
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
        $configurator->addStatusMessage(_('Error saving Config fields'),
                'error');
    }
}

$oPage->container->addItem(new H1Tag($configurator->getName()));

$oPage->container->addItem(new CustomAppConfigForm($configurator));

$oPage->addItem(new PageBottom());

$oPage->draw();
