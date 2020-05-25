<?php

/**
 * Multi FlexiBee Setup - Config fields editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\Conffield;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('App custom config Fields')));
$appId = WebPage::getRequestValue('app_id', 'int');
$companyId = WebPage::getRequestValue('company_id', 'int');

$configurator = new \FlexiPeeHP\MultiSetup\Configuration(['app_id' => $appId, 'company_id' => $companyId],['autoload'=>false]);
$configurator->setDataValue('app_id', $appId);


if ($oPage->isPosted()) {
    if ($configurator->takeData($_POST) && !is_null($configurator->saveToSQL())) {
        $configurator->addStatusMessage(_('Config fields Saved'), 'success');
    } else {
        $configurator->addStatusMessage(_('Error saving Config fields'),
                'error');
    }
}

$oPage->container->addItem(new \Ease\Html\H1Tag($configurator->getName()));

$oPage->container->addItem(new CustomAppConfigForm($configurator));

$oPage->addItem(new PageBottom());

$oPage->draw();
