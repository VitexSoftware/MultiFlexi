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

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Table;
use MultiFlexi\Servers;

require_once './init.php';
WebPage::singleton()->onlyForLogged();
WebPage::singleton()->addItem(new PageTop(_('Server list')));
$servers = new Servers();
$allFbData = $servers->getAll();
$fbtable = new Table();
$fbtable->addRowHeaderColumns([_('Type'), _('ID'), _('Name'), _('Url'), _('Username'), _('Add company')]);

foreach ($allFbData as $fbData) {
    unset($fbData['password'], $fbData['DatCreate'], $fbData['DatSave'], $fbData['ic']);

    $serverInfo = [];
    $serverInfo['type'] = new \Ease\Html\ImgTag('images/'.strtolower($fbData['type']).'.svg', $fbData['type'], ['width' => '60px']);
    $serverInfo['id'] = new \Ease\TWB4\Badge('success', $fbData['id']);
    $serverInfo['name'] = new \Ease\Html\ATag('server.php?id='.$fbData['id'], new \Ease\Html\StrongTag($fbData['name']));
    $serverInfo['url'] = new \Ease\Html\ATag($fbData['url'], $fbData['url']);
    $serverInfo['user'] = $fbData['user'];
    $serverInfo['company'] = new LinkButton('company.php?fbid='.$fbData['id'], _('Add company'), 'success');
    $fbtable->addRowColumns($serverInfo);
}

WebPage::singleton()->container->addItem(new Panel(_('Server Instances'), 'default', $fbtable, new LinkButton('server.php', _('Register new'))));
WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
