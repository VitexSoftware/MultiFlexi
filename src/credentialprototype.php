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

// Make public for development by copilot using curl: WebPage::singleton()->onlyForLogged();

$prototypeId = WebPage::getRequestValue('id', 'int');

$prototype = new \MultiFlexi\CredentialProtoType($prototypeId, ['autoload' => true]);

$delete = WebPage::getRequestValue('delete', 'int');
$removeField = WebPage::getRequestValue('removefield', 'int');

if (null !== $delete) {
    $prototype->loadFromSQL($delete);

    if ($prototype->deleteFromSQL($delete)) {
        $prototype->addStatusMessage(_('Credential Prototype removed'), 'success');
    } else {
        $prototype->addStatusMessage(_('Error removing Credential Prototype'), 'error');
    }

    WebPage::singleton()->redirect('credentialprototypes.php');
}

// Handle field deletion
if (null !== $removeField) {
    $fielder = new \MultiFlexi\CredentialProtoTypeField();

    if ($fielder->deleteFromSQL($removeField)) {
        $prototype->addStatusMessage(_('Field removed'), 'success');
    } else {
        $prototype->addStatusMessage(_('Error removing field'), 'error');
    }

    WebPage::singleton()->redirect('credentialprototype.php?id='.$prototypeId);
}

if (WebPage::singleton()->isPosted()) {
    try {
        // Generate UUID for new prototypes
        if (null === $prototypeId && empty($_POST['uuid'])) {
            $_POST['uuid'] = \Ease\Functions::guidv4();
        }

        // Separate field data from prototype data
        $newField = WebPage::getRequestValue('new_field');
        $fieldUpdates = [];
        $localized = WebPage::getRequestValue('localized');

        // Extract field updates (numeric keys represent existing field IDs)
        foreach ($_POST as $key => $value) {
            if (is_numeric($key)) {
                $fieldUpdates[$key] = $value;
                unset($_POST[$key]);
            }
        }

        // Remove field and localization data from prototype data
        unset($_POST['new_field'], $_POST['localized']);

        if ($prototype->takeData($_POST) && null !== $prototype->saveToSQL()) {
            $prototype->addStatusMessage(_('Credential Prototype Saved'), 'success');

            // Save new field if provided
            if (isset($newField) && !empty($newField['keyword']) && !empty($newField['type'])) {
                $fielder = new \MultiFlexi\CredentialProtoTypeField();
                $fieldData = array_merge($newField, ['credential_prototype_id' => $prototype->getMyKey()]);
                $fieldData['required'] = isset($newField['required']) ? 1 : 0;

                if ($fielder->takeData($fieldData) && $fielder->saveToSQL()) {
                    $prototype->addStatusMessage(_('Field added successfully'), 'success');
                } else {
                    $prototype->addStatusMessage(_('Error adding field'), 'error');
                }
            }

            // Update existing fields
            if (!empty($fieldUpdates)) {
                $fielder = new \MultiFlexi\CredentialProtoTypeField();

                foreach ($fieldUpdates as $fieldId => $fieldData) {
                    $fielder->dataReset();
                    $fieldData['required'] = isset($fieldData['required']) ? 1 : 0;
                    $fielder->takeData(array_merge($fieldData, ['id' => $fieldId]));

                    if (!$fielder->saveToSQL()) {
                        $prototype->addStatusMessage(sprintf(_('Error updating field %d'), $fieldId), 'error');
                    }
                }
            }

            // Handle localization data
            if (isset($localized) && \is_array($localized)) {
                // This would require a CredentialProtoTypeTranslations class to handle
                // For now, we'll skip translation handling until that class is created
                $prototype->addStatusMessage(_('Localization data received but not yet implemented'), 'info');
            }

            if (null === $prototypeId) {
                WebPage::singleton()->redirect('credentialprototype.php?id='.$prototype->getMyKey());
            }
        } else {
            $prototype->addStatusMessage(_('Error saving Credential Prototype'), 'error');
        }
    } catch (\PDOException $e) {
        if ($e->getCode() === 23000) { // Integrity constraint violation
            $prototype->addStatusMessage(_('Duplicate entry detected'), 'warning');
        } else {
            throw $e; // Re-throw the exception if it's not a duplicate entry error
        }
    }
}

WebPage::singleton()->addItem(new \MultiFlexi\Ui\PageTop(_('Credential Prototype')));

$prototypeForm = new CredentialProtoTypeForm($prototype);

WebPage::singleton()->container->addItem($prototypeForm);

WebPage::singleton()->addItem(new \MultiFlexi\Ui\PageBottom());

WebPage::singleton()->draw();
