<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use AbraFlexi\MultiFlexi\Ui\DbStatus;
use AbraFlexi\MultiFlexi\Ui\PageBottom;
use AbraFlexi\MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi')));

$oPage->container->addItem(new \Ease\TWB4\Panel(_('Last 20 Jobs'), 'default', new JobHistoryTable(), new DbStatus()));

$oPage->addItem(new PageBottom());

$oPage->draw();
