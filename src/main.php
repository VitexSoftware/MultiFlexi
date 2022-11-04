<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use AbraFlexi\MultiFlexi\Ui\AppsMenu;
use AbraFlexi\MultiFlexi\Ui\DbStatus;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi')));

$mainPageMenu = new AppsMenu();

$oPage->container->addItem(new \Ease\TWB4\Panel(_('Modules Availble'), 'default', $mainPageMenu, new DbStatus()));

$oPage->addItem(new PageBottom());

$oPage->draw();
