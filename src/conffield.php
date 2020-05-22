<?php

/**
 * Multi FlexiBee Setup - Config fields editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use FlexiPeeHP\MultiSetup\Conffield;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Config Fields')));
$appId = WebPage::getRequestValue('app_id', 'int');

$conffields = new Conffield(['app_id' => $appId]);


$delete = WebPage::getRequestValue('delete', 'int');
if (!is_null($delete)) {
    if ($conffields->deleteFromSQL($delete)) {
        $conffields->addStatusMessage(_('Configuration removed'));
    }
}


if ($oPage->isPosted()) {
    if ($conffields->takeData($_POST) && !is_null($conffields->saveToSQL())) {
        $conffields->addStatusMessage(_('Config field Saved'), 'success');
    } else {
        $conffields->addStatusMessage(_('Error saving Config field'),
                'error');
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag($conffields->getLink(),
            $conffields->getLink());
} else {
    $instanceName = _('App custom configuration fields');
    $instanceLink = null;
}

$instanceRow = new Row();
$instanceRow->addColumn(8, new ConfFieldsForm($conffields, new \Ease\Html\InputHiddenTag('app_id', $appId)));

$cfgs = new \Ease\Html\UlTag();

foreach ($conffields->appConfigs($appId) as $configInfo) {
    $cfgs->addItemSmart($configInfo['type'] . ' ' . $configInfo['keyname'] . ' ' . $configInfo['description'] . new \Ease\TWB4\LinkButton('?app_id=' . $appId . '&delete=' . $configInfo['id'], 'X', 'danger btn-sm'));
}

$cfgs->addItem(new \Ease\TWB4\LinkButton('app.php?id=' . WebPage::getRequestValue('app_id', 'int'), _('Back to app'), 'warning'));

$oPage->container->addItem(new Panel($instanceName, 'info', $instanceRow, $cfgs));




$oPage->addItem(new PageBottom());

$oPage->draw();
