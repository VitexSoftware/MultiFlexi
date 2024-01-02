<?php

/**
 * Multi Flexi - Config fields editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Conffield;

require_once './init.php';
$oPage->onlyForLogged();

$appId = WebPage::getRequestValue('app_id', 'int');
$confId = WebPage::getRequestValue('id', 'int');

$appliacation = new \MultiFlexi\Application($appId);

$_SESSION['application'] = $appliacation->getMyKey();

$instanceName = '';

$conffields = new Conffield($confId, ['autoload' => true]);

$conffields->setDataValue('app_id', $appId);

$delete = WebPage::getRequestValue('delete', 'int');
if (!is_null($delete)) {
    $conffields->loadFromSQL($delete);
    $cnf = new \MultiFlexi\Configuration();
    $conffields->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'name' => $conffields->getDataValue('keyname')])));

    if ($conffields->deleteFromSQL($delete)) {
        $conffields->addStatusMessage(_('Configuration removed'));
    }
}


if ($oPage->isPosted()) {
    if ($conffields->takeData($_POST) && !is_null($conffields->dbsync())) {
        $conffields->addStatusMessage(_('Config field Saved'), 'success');
    } else {
        $conffields->addStatusMessage(
            _('Error saving Config field'),
            'error'
        );
    }
}

if (strlen($instanceName)) {
    $instanceLink = new ATag(
        $conffields->getLink(),
        $conffields->getLink()
    );
} else {
    $instanceName = _('App custom configuration fields');
    $instanceLink = null;
}

$oPage->addItem(new PageTop(_('Config Fields')));

$instanceRow = new Row();
$instanceRow->addColumn(8, new ConfFieldsForm($conffields->getData(), new \Ease\Html\InputHiddenTag('app_id', $appId)));

$cfgs = new \Ease\Html\UlTag();

foreach ($conffields->appConfigs($appId) as $configInfo) {
    $cnfRow = new Row();
    $cnfRow->addColumn(2, $configInfo['type']);
    $cnfRow->addColumn(4, new ATag('conffield.php?app_id=' . $appId . '&id=' . $configInfo['id'], new \Ease\TWB4\Badge('success', $configInfo['keyname'])));
    $cnfRow->addColumn(4, $configInfo['description']);
    $cnfRow->addColumn(2, new \Ease\TWB4\LinkButton('?app_id=' . $appId . '&delete=' . $configInfo['id'], 'X', 'danger btn-sm'));
    $cfgs->addItemSmart($cnfRow);
}

$cfgs->addItem(new \Ease\TWB4\LinkButton('app.php?id=' . WebPage::getRequestValue('app_id', 'int'), [_('Back to app'), $appliacation->getRecordName()], 'warning'));

$editorRow = new \Ease\TWB4\Row();
$editorRow->addColumn(8, $instanceRow);
$editorRow->addColumn(2, new AppLogo($appliacation));

$oPage->container->addItem(new ApplicationPanel($appliacation, $editorRow, $cfgs));

$oPage->addItem(new PageBottom());

$oPage->draw();
