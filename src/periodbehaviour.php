<?php

/**
 * Multi Flexi - Periodical Tasks behaviour
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$runTemplater = new \MultiFlexi\RunTemplate();
$runTemplater->loadFromSQL($runTemplater->runTemplateID(WebPage::getRequestValue('app'), $_SESSION['company']));

$oPage->addItem(new PageTop(_('Periodical Tasks')));

$oPage->container->addItem(nl2br(print_r($runTemplater->getData(),1)) );

$oPage->addItem(new PageBottom());

$oPage->draw();
