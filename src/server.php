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

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Servers;

require_once './init.php';
WebPage::singleton()->onlyForLogged();
$servers = new Servers(WebPage::singleton()->getRequestValue('id', 'int'), ['autoload' => true]);
$instanceName = $servers->getRecordName();
$_SESSION['server'] = $servers->getMyKey();

if (WebPage::singleton()->isPosted()) {
    if ($servers->takeData($_POST) && null !== $servers->saveToSQL()) {
        $servers->addStatusMessage(_('Server instance Saved'), 'success');

        if ($servers->getDataValue('type') === 'AbraFlexi') {
            $servers->prepareRemoteAbraFlexi();
        }
    } else {
        $servers->addStatusMessage(
            _('Error saving Server instance'),
            'error',
        );
    }
}

WebPage::singleton()->addItem(new PageTop(_('Server instance')));

if (!empty($instanceName)) {
    $instanceLink = new ATag($servers->getLink(), $servers->getLink());
} else {
    $instanceName = _('New Server instance');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterServerForm($servers));
$instanceRow->addColumn(4, new \Ease\Html\ImgTag('images/'.($servers->getDataValue('type') ? strtolower((string) $servers->getDataValue('type')).'-server.svg' : 'server.svg'), $servers->getDataValue('type'), ['class' => 'img-fluid float-right', 'style' => 'height: 500px;']));
WebPage::singleton()->container->addItem(new Panel(
    $instanceName,
    'default',
    $instanceRow,
    $instanceLink,
));

if (($servers->getDataValue('type') === 'AbraFlexi') && (null === $servers->getMyKey()) === false) {
    WebPage::singleton()->container->addItem(new AbraFlexiInstanceStatus($servers));
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
