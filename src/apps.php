<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use AbraFlexi\MultiFlexi\Application;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Applications')));

$abraflexis = new Application();

$allAppData = $abraflexis->getAll();

$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('ID'), _('Enabled'), _('Image'), _('Name'), _('Description'), _('Executable'), _('Modified')]);

foreach ($allAppData as $appData) {
    $appData['image'] = new \Ease\Html\ImgTag($appData['image'], _('Icon'), ['height' => 40]);
    $fbtable->addRowColumns($appData);
}

$oPage->container->addItem(new Panel(_('AbraFlexi Instances'), 'default', $fbtable, new LinkButton('app.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
