<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Container;
use MultiFlexi\Customer;

require_once './init.php';

$oPage->onlyForLogged();

// \MultiFlexi\Engine::doThings($oPage);

$oPage->addItem(new PageTop(_('Customers')));

$oPage->addItem(new Container(new DBDataTable(new Customer())));

$oPage->addItem(new PageBottom());

$oPage->draw();
