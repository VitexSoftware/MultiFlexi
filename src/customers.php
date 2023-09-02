<?php

/**
 * Multi Flexi - Customers listing.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Customer;
use Ease\TWB4\Container;

require_once './init.php';

$oPage->onlyForLogged();

//\MultiFlexi\Engine::doThings($oPage);

$oPage->addItem(new PageTop(_('Customers')));

$oPage->addItem(new Container(new DBDataTable(new Customer())));

$oPage->addItem(new PageBottom());

$oPage->draw();
