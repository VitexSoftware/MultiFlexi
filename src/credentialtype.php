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

$addField = WebPage::getRequestValue('addField');

if (WebPage::singleton()->isPosted()) {
    $new = WebPage::getRequestValue('new');
    $class = WebPage::getRequestValue('class');

    if (empty(implode('', $new))) {
        unset($new);
    }

    unset($_POST['new']);

    $numericFields = array_filter($_POST, static function ($key) {
        return is_numeric($key);
    }, \ARRAY_FILTER_USE_KEY);

    if ($class) {
        $credTypeClass = '\\MultiFlexi\\CredentialType\\'.$class;
        /**
         * @var credentialTypeInterface Credential Type Helper class
         */
        $clasHelper = new $credTypeClass();
        $helperClassFieldsInternal = $clasHelper->fieldsInternal();
        $helperClassFieldsProvided = $clasHelper->fieldsProvided();
        $credTypeSettings = WebPage::getRequestValue($class);
        unset($_POST[$class]);
        $_POST['logo'] = $clasHelper::logo();

        foreach ($helperClassFieldsInternal as $helperInternalFieldName => $helperInternalField) {
            if ($credTypeSettings) {
                $credTypeSettings['credential_type_id'] = $crtype->getMyKey();
                $clasHelper->takeData($credTypeSettings);

                $clasHelper->addStatusMessage(sprintf(_('Saving Credential type %s options'), $credTypeClass::name()), $clasHelper->save() ? 'success' : 'error');
            }
        }

        foreach ($helperClassFieldsProvided as $helperFieldName => $helperProvidedField) {
        }
    }

    $credentialTypeData = array_diff_key($_POST, $numericFields);

    try {
        if ($credentialTypeData['company_id']) {
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
        } else {
            $crtype->addStatusMessage(_('Company must be chosen'), 'info');
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

if ($addField) {
    $columnProvided = $crtype->getHelper()->fieldsProvided()->getFieldByCode($addField);

    if (\is_object($columnProvided)) {
        $fielder->dataReset();
        $toInsert = [
            'credential_type_id' => $crtype->getMyKey(),
            'keyname' => $columnProvided->getCode(),
            'type' => $columnProvided->getType(),
            'description' => $columnProvided->getDescription(),
            'hint' => $columnProvided->getHint(),
            'defval' => $columnProvided->getDefaultValue(),
            'required' => $columnProvided->isRequired(),
            'helper' => $addField,
        ];

        $fielder->takeData($toInsert);

        $fielder->insertToSQL();
    }
}

WebPage::singleton()->addItem(new PageTop(_('Credential Type')));

if (WebPage::getRequestValue('test')) {
    WebPage::singleton()->container->addItem(new CredentialTypeCheck($crtype));
}

WebPage::singleton()->container->addItem(new CredentialTypeForm($crtype));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
