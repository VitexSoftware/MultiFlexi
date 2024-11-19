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

WebPage::singleton()->onlyForLogged();

// \MultiFlexi\Engine::doThings($oPage);

WebPage::singleton()->addItem(new PageTop(_('Customers')));

WebPage::singleton()->addItem(new Container(new DBDataTable(new Customer())));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
