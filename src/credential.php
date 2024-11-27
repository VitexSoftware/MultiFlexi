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

require_once './init.php';

WebPage::singleton()->onlyForLogged();

$credId = WebPage::getRequestValue('id', 'int');

$kredenc = new \MultiFlexi\Credential($credId);

$delete = WebPage::getRequestValue('delete', 'int');

if (null !== $delete) {
    $kredenc->loadFromSQL($delete);
    $cnf = new \MultiFlexi\Configuration();
    $kredenc->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'name' => $kredenc->getDataValue('keyname')])));

    if ($kredenc->deleteFromSQL($delete)) {
        $kredenc->addStatusMessage(_('Credential removed'));
    }
    WebPage::singleton()->redirect('companycreds.php?company_id='.$kredenc->getDataValue('company_id'));
}

if (WebPage::singleton()->isPosted()) {
    if ($kredenc->takeData($_POST) && null !== $kredenc->dbsync()) {
        $kredenc->addStatusMessage(_('Credential field Saved'), 'success');
    } else {
        $kredenc->addStatusMessage(_('Error saving Credential field'), 'error');
    }
} else {
    if(is_null(WebPage::getRequestValue('company_id')) === false){
        $kredenc->setDataValue('company_id', WebPage::getRequestValue('company_id'));
    }
}

$formType = (string) WebPage::getRequestValue('formType', 'string');

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem(new CredentialForm($kredenc));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
