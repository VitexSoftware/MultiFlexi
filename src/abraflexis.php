<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use AbraFlexi\MultiSetup\AbraFlexis;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Multi AbraFlexi')));

$abraflexis = new AbraFlexis();

$allFbData = $abraflexis->getAll();

$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('ID'), _('Name'), _('Url'), _('Username'), _('Add company')]);

foreach ($allFbData as $fbData) {
    unset($fbData['password']);
    unset($fbData['DatCreate']);
    unset($fbData['DatSave']);
    unset($fbData['ic']);

    $fbData['name'] = new \Ease\Html\ATag('abraflexi.php?id=' . $fbData['id'], new \Ease\Html\StrongTag($fbData['name']));
    $fbData['url'] = new \Ease\Html\ATag($fbData['url'], $fbData['url']);
    $fbData['company'] = new LinkButton('company.php?fbid=' . $fbData['id'], _('Add company'), 'success');
    $fbtable->addRowColumns($fbData);
}

$oPage->container->addItem(new Panel(_('AbraFlexi Instances'), 'default', $fbtable, new LinkButton('abraflexi.php', _('Register new'))));

$oPage->addItem(new PageBottom());

$oPage->draw();
