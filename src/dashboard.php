<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\PageBottom;
use MultiFlexi\Ui\PageTop;

require_once './init.php';

$oPage->addItem(new PageTop(_('Dashboard')));

$oPage->container->addItem(new JobChart(new \MultiFlexi\Job(), ['id' => 'container']));

$oPage->addItem(new PageBottom());

$oPage->draw();
