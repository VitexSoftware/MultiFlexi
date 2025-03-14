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
$removeField = WebPage::getRequestValue('removefield', 'int');

if (null !== $delete) {
    $crtype->loadFromSQL($delete);
    $cnf = new \MultiFlexi\Configuration();
    $crtype->addStatusMessage(sprintf(_('%d used configurations removed'), $cnf->deleteFromSQL(['app_id' => $appId, 'name' => $crtype->getDataValue('keyname')])));

    if ($crtype->deleteFromSQL($delete)) {
        $crtype->addStatusMessage(_('Credential removed'));
    }

    WebPage::singleton()->redirect('credentialtypes.php');
}

$fielder = new \MultiFlexi\CrTypeField();

if (null !== $removeField) {
    if ($fielder->deleteFromSQL($removeField)) {
        $crtype->addStatusMessage(_('Field removed'), 'success');
    } else {
        $crtype->addStatusMessage(_('Error removing field'), 'error');
    }

    WebPage::singleton()->redirect('credentialtype.php?id='.$credTypeId);
}

if (WebPage::singleton()->isPosted()) {
    $new = WebPage::getRequestValue('new');

    if (empty(implode('', $new))) {
        unset($new);
    }

    unset($_POST['new']);

    $numericFields = array_filter($_POST, static function ($key) {
        return is_numeric($key);
    }, \ARRAY_FILTER_USE_KEY);

    $credentialTypeData = array_diff_key($_POST, $numericFields);

    try {
        if ($crtype->takeData($credentialTypeData) && null !== $crtype->dbsync()) {
            $crtype->addStatusMessage(_('Credential field Saved'), 'success');

            if (isset($new) && \array_key_exists('keyname', $new) && \strlen($new['keyname'])) {
                $fielder->takeData($new);
                $fielder->setDataValue('credential_type_id', $crtype->getMyKey());
                $fielder->insertToSQL();
            }

            if (isset($numericFields)) {
                foreach ($numericFields as $columnId => $fields) {
                    $fielder = new \MultiFlexi\CrTypeField();
                    $fielder->takeData(array_merge($fields, ['id' => $columnId]));
                    $fielder->updateToSQL();
                }
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

WebPage::singleton()->addItem(new PageTop(_('Credential Type')));

WebPage::singleton()->container->addItem(new CredentialTypeForm($crtype));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
