<?php

/**
 * Multi Flexi - Index page.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use MultiFlexi\Servers;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Server list')));
$servers = new Servers();
$allFbData = $servers->getAll();
$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('Type'), _('ID'), _('Name'), _('Url'), _('Username'), _('Add company')]);
foreach ($allFbData as $serverId => $fbData) {
    unset($fbData['password']);
    unset($fbData['DatCreate']);
    unset($fbData['DatSave']);
    unset($fbData['ic']);
    $serverInfo = [];
    $serverInfo['type'] = new \Ease\Html\ImgTag('images/' . strtolower($fbData['type']) . '.svg', $fbData['type'], ['width' => '60px']);
    $serverInfo['id'] = new \Ease\TWB4\Badge('success', $fbData['id']);
    $serverInfo['name'] = new \Ease\Html\ATag('server.php?id=' . $fbData['id'], new \Ease\Html\StrongTag($fbData['name']));
    $serverInfo['url'] = new \Ease\Html\ATag($fbData['url'], $fbData['url']);
    $serverInfo['user'] = $fbData['user'];
    $serverInfo['company'] = new LinkButton('company.php?fbid=' . $fbData['id'], _('Add company'), 'success');
    $fbtable->addRowColumns($serverInfo);
}

$oPage->container->addItem(new Panel(_('Server Instances'), 'default', $fbtable, new LinkButton('server.php', _('Register new'))));
$oPage->addItem(new PageBottom());
$oPage->draw();
