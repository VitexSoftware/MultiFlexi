<?php

/**
 * Multi FlexiBee Setup - Config fields editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use AbraFlexi\MultiSetup\Conffield;

require_once './init.php';
$oPage->onlyForLogged();
$oPage->addItem(new PageTop(_('Config Fields')));
$appId = WebPage::getRequestValue('app_id', 'int');
$confId = WebPage::getRequestValue('id', 'int');

$instanceName = '';

$conffields = new Conffield($confId);
$conffields->setDataValue('app_id', $appId);

$delete = WebPage::getRequestValue('delete', 'int');
if (!is_null($delete)) {
    $conffields->loadFromSQL($delete);
    $cnf = new \AbraFlexi\MultiSetup\Configuration();
    $conffields->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'key' => $conffields->getDataValue('keyname')])));

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
    $cnfRow = new Row();
    $cnfRow->addColumn(2, $configInfo['type']);
    $cnfRow->addColumn(4, new \Ease\TWB4\Badge('success', $configInfo['keyname']));
    $cnfRow->addColumn(4, $configInfo['description']);
    $cnfRow->addColumn(2, new \Ease\TWB4\LinkButton('?app_id=' . $appId . '&delete=' . $configInfo['id'], 'X', 'danger btn-sm'));
    $cfgs->addItemSmart($cnfRow);
}

$cfgs->addItem(new \Ease\TWB4\LinkButton('app.php?id=' . WebPage::getRequestValue('app_id', 'int'), _('Back to app'), 'warning'));

$oPage->container->addItem(new Panel($instanceName, 'info', $instanceRow, $cfgs));


$oPage->addItem(new PageBottom());

$oPage->draw();
