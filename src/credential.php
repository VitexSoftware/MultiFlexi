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

    $deleted = (new \MultiFlexi\RunTplCreds())->deleteFromSQL(['credentials_id' => $delete]);
    $kredenc->addStatusMessage(_('%s runtemplates affected'), $deleted ? 'warning' : 'info');

    if ($kredenc->deleteFromSQL($delete)) {
        $kredenc->addStatusMessage(_('Credential removed'));
    }

    WebPage::singleton()->redirect('companycreds.php?company_id='.$kredenc->getDataValue('company_id'));
}

if (WebPage::singleton()->isPosted()) {
    try {
        if ($kredenc->takeData($_POST) && null !== $kredenc->dbsync()) {
            $kredenc->addStatusMessage(_('Credential field Saved'), 'success');
        }
    } catch (\PDOException $exc) {
        $kredenc->addStatusMessage(_('Error saving Credential field'), 'error');
    }
} else {
    $forcedCredTypeId = WebPage::getRequestValue('credential_type_id');

    if ($forcedCredTypeId) {
        $kredenc->setDataValue('credential_type_id', $forcedCredTypeId);
    }

    if ((null === WebPage::getRequestValue('company_id')) === false) {
        $kredenc->setDataValue('company_id', WebPage::getRequestValue('company_id'));
    }

    if ((null === WebPage::getRequestValue('formType')) === false) {
        $kredenc->setDataValue('formType', WebPage::getRequestValue('formType'));
    }
}

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

WebPage::singleton()->container->addItem(new CredentialForm($kredenc));

if ($kredenc->getMyKey() !== null) {
    WebPage::singleton()->container->addItem(new CredentialCloneForm($kredenc));
}

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
