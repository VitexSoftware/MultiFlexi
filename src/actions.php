<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi - Action Modules')));

$modConf = new \MultiFlexi\ModConfig();

if (\Ease\WebPage::isFormPosted()) {
    $modConf->saveFormData($_POST);
}

$oPage->container->addItem(new \Ease\TWB4\Panel(new \Ease\Html\H2Tag(_('Installed Action Modules')), 'default', new ActionsAdministration($modConf)));

$oPage->addItem(new PageBottom());

$oPage->draw();
