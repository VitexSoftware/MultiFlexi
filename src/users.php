<?php

/**
 * Multi Flexi - Users listing.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * TaxTorro - Přehled uživatelů.
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015 Vitex Software
 */
require_once 'includes/Init.php';

$oPage->onlyForLogged();

Engine::doThings($oPage);

$oPage->addItem(new ui\PageTop(_('Users')));

$oPage->addItem(new \Ease\TWB4\Container(new DataGrid(_('Users'), new User())));

$oPage->addItem(new ui\PageBottom());

$oPage->draw();
