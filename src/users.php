<?php

namespace TaxTorro;

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
