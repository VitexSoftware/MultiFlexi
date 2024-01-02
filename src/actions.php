<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi - Action Modules')));

$oPage->container->addItem(new \Ease\TWB4\Panel(new \Ease\Html\H2Tag(_('Installed Executor Modules')), 'default', new ActionsListing()));

$oPage->addItem(new PageBottom());

$oPage->draw();
