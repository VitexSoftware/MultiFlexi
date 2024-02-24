<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Servers;

require_once './init.php';
$oPage->onlyForLogged();
$servers = new Servers($oPage->getRequestValue('id', 'int'), ['autoload' => true]);
$instanceName = $servers->getRecordName();
$_SESSION['server'] = $servers->getMyKey();

if ($oPage->isPosted()) {
    if ($servers->takeData($_POST) && !is_null($servers->saveToSQL())) {
        $servers->addStatusMessage(_('Server instance Saved'), 'success');
        if ($servers->getDataValue('type') == 'AbraFlexi') {
            $servers->prepareRemoteAbraFlexi();
        }
    } else {
        $servers->addStatusMessage(
            _('Error saving Server instance'),
            'error'
        );
    }
}

$oPage->addItem(new PageTop(_('Server instance')));
if (!empty($instanceName)) {
    $instanceLink = new ATag($servers->getLink(), $servers->getLink());
} else {
    $instanceName = _('New Server instance');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterServerForm($servers));
$instanceRow->addColumn(4, new \Ease\Html\ImgTag('images/' . ($servers->getDataValue('type') ? strtolower(strval($servers->getDataValue('type'))) . '-server.svg' : 'server.svg' ), $servers->getDataValue('type'), ['class' => 'img-fluid float-right', 'style' => 'height: 500px;']));
$oPage->container->addItem(new Panel(
    $instanceName,
    'default',
    $instanceRow,
    $instanceLink
));
if (($servers->getDataValue('type') == 'AbraFlexi') && is_null($servers->getMyKey()) === false) {
    $oPage->container->addItem(new AbraFlexiInstanceStatus($servers));
}

$oPage->addItem(new PageBottom());
$oPage->draw();
