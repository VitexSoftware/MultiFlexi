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

WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi - Environment Modules')));

WebPage::singleton()->container->addItem(new \Ease\TWB4\Panel(new \Ease\Html\H2Tag(_('Installed Environment Modules')), 'default', new EnvModulesListing()));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
