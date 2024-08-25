<?php

/**
 * MultiFlexi - Template page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\DbStatus;
use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi')));

$oPage->container->addItem('put content here');

$oPage->addItem(new PageBottom());

$oPage->draw();
