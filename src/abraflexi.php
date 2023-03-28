<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\AbraFlexis;

require_once './init.php';
$oPage->onlyForLogged();

$abraflexis = new \AbraFlexis($oPage->getRequestValue('id', 'int'), ['autoload' => true]);
$instanceName = $abraflexis->getRecordName();
$_SESSION['server'] = &$abraflexis;

$oPage->addItem(new PageTop(_('AbraFlexi instance')));

if ($oPage->isPosted()) {
    if ($abraflexis->takeData($_POST) && !is_null($abraflexis->saveToSQL())) {
        $abraflexis->addStatusMessage(_('AbraFlexi instance Saved'), 'success');
        $abraflexis->prepareRemoteAbraFlexi();
    } else {
        $abraflexis->addStatusMessage(_('Error saving AbraFlexi instance'),
                'error');
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag($abraflexis->getLink(),
            $abraflexis->getLink());
} else {
    $instanceName = _('New AbraFlexi instance');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new RegisterAbraFlexiForm($abraflexis));

$oPage->container->addItem(new Panel($instanceName, 'info',
                $instanceRow, $instanceLink));

if (!is_null($abraflexis->getMyKey())) {
    $oPage->container->addItem(new \AbraFlexiInstanceStatus($abraflexis));
}

$oPage->addItem(new PageBottom());

$oPage->draw();
