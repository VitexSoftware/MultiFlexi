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
use Ease\TWB4\Row;
use MultiFlexi\Conffield;

require_once './init.php';
WebPage::singleton()->onlyForLogged();

$appId = WebPage::getRequestValue('app_id', 'int');
$confId = WebPage::getRequestValue('id', 'int');

$appliacation = new \MultiFlexi\Application($appId);

$_SESSION['application'] = $appliacation->getMyKey();

$instanceName = '';

$conffields = new Conffield($confId, ['autoload' => true]);

$conffields->setDataValue('app_id', $appId);

$delete = WebPage::getRequestValue('delete', 'int');

if (null !== $delete) {
    $conffields->loadFromSQL($delete);
    $cnf = new \MultiFlexi\Configuration();
    $conffields->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'name' => $conffields->getDataValue('keyname')])));

    if ($conffields->deleteFromSQL($delete)) {
        $conffields->addStatusMessage(_('Configuration removed'));
    }
}

if (WebPage::singleton()->isPosted()) {
    if ($conffields->takeData($_POST) && null !== $conffields->dbsync()) {
        $conffields->addStatusMessage(_('Config field Saved'), 'success');
    } else {
        $conffields->addStatusMessage(
            _('Error saving Config field'),
            'error',
        );
    }
}

if (\strlen($instanceName)) {
    $instanceLink = new ATag(
        $conffields->getLink(),
        $conffields->getLink(),
    );
} else {
    $instanceName = _('App custom configuration fields');
    $instanceLink = null;
}

WebPage::singleton()->addItem(new PageTop(_('Config Fields')));

$instanceRow = new Row();
$instanceRow->addColumn(8, new ConfFieldsForm($conffields->getData(), new \Ease\Html\InputHiddenTag('app_id', $appId)));

$cfgs = new \Ease\Html\UlTag();

foreach ($conffields->appConfigs($appId) as $configInfo) {
    $cnfRow = new Row();
    $cnfRow->addColumn(2, $configInfo['type']);
    $cnfRow->addColumn(4, new ATag('conffield.php?app_id='.$appId.'&id='.$configInfo['id'], new \Ease\TWB4\Badge('success', $configInfo['keyname'])));
    $cnfRow->addColumn(4, $configInfo['description']);
    $cnfRow->addColumn(2, new \Ease\TWB4\LinkButton('?app_id='.$appId.'&delete='.$configInfo['id'], 'X', 'danger btn-sm'));
    $cfgs->addItemSmart($cnfRow);
}

$cfgs->addItem(new \Ease\TWB4\LinkButton('app.php?id='.WebPage::getRequestValue('app_id', 'int'), [_('Back to app'), $appliacation->getRecordName()], 'warning'));

$editorRow = new \Ease\TWB4\Row();
$editorRow->addColumn(8, $instanceRow);
$editorRow->addColumn(2, new AppLogo($appliacation));

WebPage::singleton()->container->addItem(new ApplicationPanel($appliacation, $editorRow, $cfgs));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
