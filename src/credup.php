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

use Ease\Html\DivTag;
use Ease\TWB5\Panel;
use MultiFlexi\Credential;
use MultiFlexi\CredentialType;

/**
 * Convert the "Old style credential" to new Dynamic style:
 *
 * 1. Load old credential data using \MultiFlexi\Credential() class. The class is operating upon the `credentials` table with the following structure:
 *
 * CREATE TABLE `credentials` (
 * `id` int(11) UNSIGNED NOT NULL,
 * `name` varchar(255) DEFAULT NULL,
 * `company_id` int(11) DEFAULT NULL,
 * `formType` varchar(255) DEFAULT NULL,
 * `credential_type_id` int(11) UNSIGNED NOT NULL
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
 *
 * 2. Create new \MultiFlexi\CredentialType() with `formType` value from \MultiFlexi\Credential() as its `class` field
 * 3. Update the `credential_type_id` in the `credentials` table to the new \MultiFlexi\CredentialType() ID
 * 4. Update the `formType` in the `credentials` table to null
 */
WebPage::singleton()->onlyForLogged();

WebPage::singleton()->addItem(new PageTop(_('MultiFlexi')));

// Conversion logic
$messages = [];
$oldCreds = (new Credential())->listingQuery()->fetchAll();

if (empty($oldCreds)) {
    $messages[] = new DivTag('<div class="alert alert-success">No old style credentials found.</div>');
} else {
    $messages[] = new DivTag('<div class="alert alert-warning">Found '.\count($oldCreds).' old style credentials. Starting conversion...</div>');
    $converted = 0;
    $credObj = new Credential();

    foreach ($oldCreds as $cred) {
        if ($cred['credential_type_id'] === 0) {
            $formType = $cred['formType'];

            if (!$formType) {
                continue;
            }

            // Try to find or create CredentialType for this formType
            $credType = (new CredentialType())->listingQuery()->where(['class' => $formType, 'company_id' => $cred['company_id']])->fetch();

            if (!$credType) {
                $credTypeObj = new CredentialType();
                $credTypeObj->takeData([
                    'name' => $formType.' '.(new \MultiFlexi\Company($cred['company_id']))->getRecordName(),
                    'class' => $formType,
                    'company_id' => $cred['company_id'] ?? null,
                ]);
                $credTypeId = $credTypeObj->insertToSQL();
                $credType = $credTypeObj->getData();

                $fields = $credTypeObj->getHelper()->fieldsProvided();
                $fielder = new \MultiFlexi\CrTypeField();

                foreach ($fields as $addField) {
                    $fielder->dataReset();
                    $toInsert = [
                        'credential_type_id' => $credTypeId,
                        'keyname' => $addField->getCode(),
                        'type' => $addField->getType(),
                        'description' => $addField->getDescription(),
                        'hint' => $addField->getHint(),
                        'defval' => $addField->getDefaultValue(),
                        'required' => $addField->isRequired(),
                        'helper' => $addField->getName(),
                    ];

                    $fielder->takeData($toInsert);

                    try {
                        $fielder->insertToSQL();
                    } catch (\PDOException $exc) {
                        $fielder->addStatusMessage(sprintf(_('Column %s not added to %s'), $addField->getCode(), $credTypeObj->getRecordName()), 'error');
                    }
                }
            } else {
                $credTypeId = $credType['id'];
            }

            // Update credential
            $credObj->updateToSQL(['credential_type_id' => $credTypeId, 'formType' => null], ['id' => $cred['id']]);

            ++$converted;
        }

        $messages[] = new DivTag('<div class="alert alert-success">Converted '.$converted.' credentials to new style.</div>');
    }
}

// Always add to WebPage directly to avoid container property error
WebPage::singleton()->addItem(new Panel(_('Credential Conversion'), 'info', $messages));

WebPage::singleton()->addItem(new PageBottom());

WebPage::singleton()->draw();
