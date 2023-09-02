<?php

/**
 * Multi Flexi - Users listing.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace MultiFlexi\Ui;

require_once './init.php';

$oPage->onlyForLogged();

//Engine::doThings($oPage);

$oPage->addItem(new PageTop(_('Users')));

$oPage->addItem(new \Ease\TWB4\Container(new DBDataTable(new \AbraFlexi\MultiFlexi\User())));

$oPage->addItem(new PageBottom());

$oPage->draw();
