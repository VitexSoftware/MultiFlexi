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

$oPage->addItem(new ui\PageTop(_('Přehled uživatelů')));

$oPage->addItem(new \Ease\TWB\Container(new DataGrid(_('Uživatelé'), new User())));

$oPage->addItem(new ui\PageBottom());

$oPage->draw();
