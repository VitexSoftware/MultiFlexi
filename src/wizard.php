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

$oPage->addItem(new PageTop(_('MultiFlexi')));

$oPage->container->addItem(new ConfigurationWizard(new \MultiFlexi\Company(WebPage::getRequestValue('company', 'int'))));

$oPage->addItem(new PageBottom());

$oPage->draw();