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

$stepParam = WebPage::getRequestValue('step');

if ($stepParam === 'complete') {
    $step = $stepParam;
} else {
    $step = (int) $stepParam ?: 1;
}

// Reset wizard data when starting fresh (step 1 without POST)
if ($step === 1 && !WebPage::singleton()->isPosted()) {
    $resetParam = WebPage::getRequestValue('reset');

    if ($resetParam === '1' || !isset($_SESSION['credential_wizard'])) {
        CredentialWizard::clearWizardData();
    }
}

// Handle form submission and process wizard data
if (WebPage::singleton()->isPosted()) {
    $postData = $_POST;

    switch ($step) {
        case 2:
            // Step 1 -> Step 2: Save company selection
            if (isset($postData['company_id'])) {
                CredentialWizard::updateWizardData(['company_id' => (int) $postData['company_id']]);
            }

            break;
        case 3:
            // Step 2 -> Step 3: Save credential prototype selection
            if (isset($postData['credential_prototype_id'])) {
                CredentialWizard::updateWizardData([
                    'company_id' => (int) $postData['company_id'],
                    'credential_prototype_id' => (int) $postData['credential_prototype_id'],
                ]);
            }

            break;
        case 4:
            // Step 3 -> Step 4: Save or create credential type
            if (isset($postData['company_id'], $postData['credential_prototype_id'], $postData['credential_type_choice'])) {
                $companyId = (int) $postData['company_id'];
                $prototypeId = (int) $postData['credential_prototype_id'];

                if ($postData['credential_type_choice'] === 'new') {
                    // Create new credential type
                    $credentialType = new \MultiFlexi\CredentialType();
                    $credentialType->setDataValue('company_id', $companyId);
                    $credentialType->setDataValue('name', $postData['new_credential_type_name'] ?? 'New Credential Type');

                    // Get prototype to copy fields
                    $prototype = new \MultiFlexi\CredentialProtoType($prototypeId);
                    
                    if ($credentialType->dbsync()) {
                        $credentialTypeId = $credentialType->getMyKey();
                        
                        // Copy fields from prototype to new credential type
                        $prototypeFielder = new \MultiFlexi\CredentialProtoTypeField();
                        $prototypeFields = $prototypeFielder->listingQuery()
                            ->where('credential_prototype_id', $prototypeId)
                            ->fetchAll();
                        
                        $typeFielder = new \MultiFlexi\CrTypeField();
                        foreach ($prototypeFields as $protoField) {
                            $typeFielder->dataReset();
                            $typeFielder->takeData([
                                'credential_type_id' => $credentialTypeId,
                                'keyname' => $protoField['keyword'],
                                'type' => $protoField['type'],
                                'description' => $protoField['description'] ?? '',
                                'required' => $protoField['required'] ?? 0,
                            ]);
                            $typeFielder->insertToSQL();
                        }
                        
                        CredentialWizard::updateWizardData([
                            'company_id' => $companyId,
                            'credential_prototype_id' => $prototypeId,
                            'credential_type_id' => $credentialTypeId,
                        ]);
                        $credentialType->addStatusMessage(_('Credential Type created successfully'), 'success');
                    } else {
                        $credentialType->addStatusMessage(_('Error creating Credential Type'), 'error');
                        $step = 3; // Stay on step 3
                    }
                } else {
                    // Use existing credential type
                    $credentialTypeId = (int) $postData['credential_type_choice'];
                    CredentialWizard::updateWizardData([
                        'company_id' => $companyId,
                        'credential_prototype_id' => $prototypeId,
                        'credential_type_id' => $credentialTypeId,
                    ]);
                }
            }

            break;
        case 'complete':
            // Step 4 -> Complete: Create credential
            if (isset($postData['company_id'], $postData['credential_type_id'], $postData['name'])) {
                $credential = new \MultiFlexi\Credential();
                $credential->setDataValue('company_id', (int) $postData['company_id']);
                $credential->setDataValue('credential_type_id', (int) $postData['credential_type_id']);
                $credential->setDataValue('name', $postData['name']);

                // Get credential type fields
                $credentialType = new \MultiFlexi\CredentialType((int) $postData['credential_type_id']);
                $fields = $credentialType->getFields();

                // Save credential
                if ($credential->dbsync()) {
                    $credentialId = $credential->getMyKey();

                    // Save field values
                    $vault = new \MultiFlexi\CredentialConfigFields($credential);
                    foreach ($fields->getFields() as $field) {
                        $fieldName = $field->getCode();
                        if (isset($postData[$fieldName])) {
                            $vault->setDataValue($fieldName, $postData[$fieldName]);
                        }
                    }
                    $vault->saveToSQL();

                    CredentialWizard::updateWizardData(['credential_id' => $credentialId]);
                    $credential->addStatusMessage(_('Credential created successfully'), 'success');
                    
                    // Clear wizard data and redirect
                    CredentialWizard::clearWizardData();
                    WebPage::singleton()->redirect('credential.php?id='.$credentialId);
                } else {
                    $credential->addStatusMessage(_('Error creating Credential'), 'error');
                    $step = 4; // Stay on step 4
                }
            }

            break;
    }
}

WebPage::singleton()->addItem(new PageTop(_('Credential Creation Wizard')));

// Add custom CSS for wizard (similar to activation wizard)
WebPage::singleton()->addCss(<<<'EOD'

.credential-wizard .wizard-steps {
    margin-bottom: 2rem;
}

.credential-wizard .wizard-content {
    min-height: 400px;
}

.credential-wizard .card {
    transition: all 0.3s ease;
}

.credential-wizard .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.credential-wizard .card.border-primary {
    border-width: 2px !important;
}

.credential-wizard .wizard-navigation {
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

EOD);

// Show wizard
if ($step === 'complete' || (isset($_SESSION['credential_wizard']['credential_id']) && $_SESSION['credential_wizard']['credential_id'])) {
    // Redirect to credential page after completion
    if (isset($_SESSION['credential_wizard']['credential_id'])) {
        $credentialId = $_SESSION['credential_wizard']['credential_id'];
        CredentialWizard::clearWizardData();
        WebPage::singleton()->redirect('credential.php?id='.$credentialId);
    }
} else {
    // Convert step to int for wizard
    $wizardStep = \is_int($step) ? $step : 1;
    $wizard = new CredentialWizard($wizardStep);
    WebPage::singleton()->container->addItem($wizard);
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
