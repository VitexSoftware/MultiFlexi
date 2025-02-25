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

$credTypeId = WebPage::getRequestValue('id', 'int');

$crtype = new \MultiFlexi\CredentialType($credTypeId, ['autoload' => true]);

$delete = WebPage::getRequestValue('delete', 'int');

if (null !== $delete) {
    $crtype->loadFromSQL($delete);
    $cnf = new \MultiFlexi\Configuration();
    $crtype->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'name' => $crtype->getDataValue('keyname')])));

    if ($crtype->deleteFromSQL($delete)) {
        $crtype->addStatusMessage(_('Credential removed'));
    }

    WebPage::singleton()->redirect('credentialtypes.php');
}

if (WebPage::singleton()->isPosted()) {
    $new = WebPage::getRequestValue('new');

    if (empty(implode('', $new))) {
        unset($new);
    }

    unset($_POST['new']);
    $uuid = WebPage::getRequestValue('uuid');

    if (empty($uuid)) {
        unset($_POST['uuid']);
    }

    try {
        if ($crtype->takeData($_POST) && null !== $crtype->dbsync()) {
            $crtype->addStatusMessage(_('Credential field Saved'), 'success');

            if (isset($new)) {
                $saver = new \MultiFlexi\CrTypeField();
                $saver->takeData($new);
                $saver->setDataValue('credential_type_id', $crtype->getMyKey());
                $saver->insertToSQL();
            }
        } else {
            $crtype->addStatusMessage(_('Error saving Credential field'), 'error');
        }
    } catch (\PDOException $e) {
        if ($e->getCode() === 23000) { // Integrity constraint violation
            $crtype->addStatusMessage(_('Duplicate entry detected'), 'warning');
        } else {
            throw $e; // Re-throw the exception if it's not a duplicate entry error
        }
    }
} else {
    if ((null === WebPage::getRequestValue('company_id')) === false) {
        $crtype->setDataValue('company_id', WebPage::getRequestValue('company_id'));
    }

    if ((null === WebPage::getRequestValue('formType')) === false) {
        $crtype->setDataValue('formType', WebPage::getRequestValue('formType'));
    }
}

WebPage::singleton()->addItem(new PageTop(_('Crednetial Type')));

WebPage::singleton()->container->addItem(new CredentialTypeForm($crtype));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
