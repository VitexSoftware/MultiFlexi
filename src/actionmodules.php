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

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi - Action Modules')));

$modConf = new \MultiFlexi\ModConfig();

if (\Ease\WebPage::isFormPosted()) {
    $modConf->saveFormData($_POST);
}

WebPage::singleton()->container->addItem(new \Ease\TWB5\Panel(new \Ease\Html\H2Tag(_('Installed Action Modules')), 'default', new ActionsAdministration($modConf)));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
