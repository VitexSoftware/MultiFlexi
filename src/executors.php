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

require_once './init.php';

$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Multi Flexi - Executor Modules')));

$oPage->container->addItem(new \Ease\TWB4\Panel(new \Ease\Html\H2Tag(_('Installed Executor Modules')), 'default', new ExecutorsListing()));

$oPage->addItem(new PageBottom());

$oPage->draw();
