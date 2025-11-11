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

if ($stepParam === 'finish' || $stepParam === 'complete') {
    $step = $stepParam;
} else {
    $step = (int) $stepParam ?: 1;
}

// Reset wizard data when starting fresh (step 1 without POST)
if ($step === 1 && !WebPage::singleton()->isPosted()) {
    $resetParam = WebPage::getRequestValue('reset');

    if ($resetParam === '1' || !isset($_SESSION['activation_wizard'])) {
        ActivationWizard::clearWizardData();
    }
}

// Handle form submission and process wizard data
if (WebPage::singleton()->isPosted()) {
    $postData = $_POST;

    switch ($step) {
        case 2:
            // Step 1 -> Step 2: Save company selection
            if (isset($postData['company_id'])) {
                ActivationWizard::updateWizardData(['company_id' => (int) $postData['company_id']]);
            }

            break;
        case 3:
            // Step 2 -> Step 3: Save application selection
            if (isset($postData['app_id'])) {
                ActivationWizard::updateWizardData([
                    'company_id' => (int) $postData['company_id'],
                    'app_id' => (int) $postData['app_id'],
                ]);
            }

            break;
        case 4:
            // Step 3 -> Step 4: Create RunTemplate
            if (isset($postData['company_id'], $postData['app_id'], $postData['runtemplate_name'])) {
                $runTemplate = new \MultiFlexi\RunTemplate();
                $runTemplate->setDataValue('app_id', (int) $postData['app_id']);
                $runTemplate->setDataValue('company_id', (int) $postData['company_id']);
                $runTemplate->setDataValue('name', $postData['runtemplate_name']);
                $runTemplate->setDataValue('interv', $postData['interv'] ?? 'n');

                // Save note if provided
                if (isset($postData['runtemplate_note'])) {
                    $runTemplate->setDataValue('note', $postData['runtemplate_note']);
                }

                $saveResult = $runTemplate->dbsync();

                if ($saveResult) {
                    $runtemplateId = $runTemplate->getMyKey();
                    ActivationWizard::updateWizardData([
                        'company_id' => (int) $postData['company_id'],
                        'app_id' => (int) $postData['app_id'],
                        'runtemplate_name' => $postData['runtemplate_name'],
                        'runtemplate_note' => $postData['runtemplate_note'] ?? '',
                        'runtemplate_id' => $runtemplateId,
                    ]);
                    $runTemplate->addStatusMessage(_('RunTemplate created successfully'), 'success');
                } else {
                    $runTemplate->addStatusMessage(_('Error creating RunTemplate'), 'error');
                    $step = 3; // Stay on step 3
                }
            }

            break;
        case 5:
            // Step 4 -> Step 5: Assign credentials
            if (isset($postData['runtemplate_id'])) {
                $runtemplateId = (int) $postData['runtemplate_id'];

                // Only process credentials if they are provided
                if (isset($postData['credential']) && \is_array($postData['credential'])) {
                    $rtplcrd = new \MultiFlexi\RunTplCreds();

                    // Get currently assigned credentials
                    $existingBindings = [];
                    $existingQuery = $rtplcrd->listingQuery()->where('runtemplate_id', $runtemplateId)->fetchAll();

                    foreach ($existingQuery as $binding) {
                        $existingBindings[$binding['credentials_id']] = true;
                    }

                    foreach ($postData['credential'] as $reqType => $credId) {
                        if ($credId && is_numeric($credId)) {
                            $credIdInt = (int) $credId;

                            // Only bind if not already bound
                            if (!isset($existingBindings[$credIdInt])) {
                                $rtplcrd->bind($runtemplateId, $credIdInt, $reqType);
                            }
                        }
                    }
                }

                ActivationWizard::updateWizardData(['credentials_assigned' => true]);
            }

            break;
        case 6:
            // Step 5 -> Step 6: Save configuration
            if (isset($postData['runtemplate_id'])) {
                $runtemplateId = (int) $postData['runtemplate_id'];
                $runTemplate = new \MultiFlexi\RunTemplate($runtemplateId);
                $app = new \MultiFlexi\Application($runTemplate->getDataValue('app_id'));
                $company = new \MultiFlexi\Company($runTemplate->getDataValue('company_id'));

                // Get application configs to know what fields are valid
                $confField = new \MultiFlexi\Conffield();
                $appConfigs = $confField->getAppConfigs($app);
                $validFields = array_keys($appConfigs->getFields());

                // Filter only valid configuration fields
                $cleanedPostData = [];

                foreach ($postData as $key => $value) {
                    if (\in_array($key, $validFields, true)) {
                        $cleanedPostData[$key] = $value;
                    }
                }

                if (!empty($cleanedPostData)) {
                    $configurator = new \MultiFlexi\Configuration([
                        'runtemplate_id' => $runtemplateId,
                        'app_id' => $app->getMyKey(),
                        'company_id' => $company->getMyKey(),
                    ], ['autoload' => false]);

                    if ($configurator->takeData($cleanedPostData) && null !== $configurator->saveToSQL()) {
                        $configurator->addStatusMessage(_('Configuration saved successfully'), 'success');
                        ActivationWizard::updateWizardData(['configuration_saved' => true]);
                    } else {
                        $configurator->addStatusMessage(_('Error saving configuration'), 'error');
                        $step = 5; // Stay on step 5
                    }
                } else {
                    // No configuration fields to save
                    ActivationWizard::updateWizardData(['configuration_saved' => true]);
                }
            }

            break;
        case 7:
            // Step 6 -> Step 7: Save actions and show summary
            if (isset($postData['runtemplate_id']) && !empty($postData['runtemplate_id'])) {
                $runtemplateId = (int) $postData['runtemplate_id'];
                $runTemplate = new \MultiFlexi\RunTemplate($runtemplateId);
                $app = new \MultiFlexi\Application($runTemplate->getDataValue('app_id'));
                $company = new \MultiFlexi\Company($runTemplate->getDataValue('company_id'));

                // Save actions
                $successActions = ActionsChooser::toggles('success');
                $failActions = ActionsChooser::toggles('fail');
                $runTemplate->setDataValue('fail', serialize($failActions));
                $runTemplate->setDataValue('success', serialize($successActions));

                // Only save if we have valid data (app_id exists)
                if ($runTemplate->getDataValue('app_id') && $runTemplate->getMyKey()) {
                    $runTemplate->saveToSQL();

                    // Save action configurations
                    $actions = new \MultiFlexi\ActionConfig();
                    $actions->saveModeConfigs('success', ActionsChooser::formModuleCofig('success'), $runTemplate->getMyKey());
                    $actions->saveModeConfigs('fail', ActionsChooser::formModuleCofig('fail'), $runTemplate->getMyKey());

                    // Success message
                    $successMessage = sprintf(
                        _('RunTemplate #%d "%s" successfully created for application "%s" and company "%s".'),
                        $runTemplate->getMyKey(),
                        $runTemplate->getRecordName(),
                        $app->getRecordName(),
                        $company->getRecordName(),
                    );
                    $runTemplate->addStatusMessage($successMessage, 'success');

                    // Don't clear wizard data yet, we need it for summary page (step 7)
                }
            }

            break;
    }
}

WebPage::singleton()->addItem(new PageTop(_('Application Activation Wizard')));

// Add custom CSS for wizard
WebPage::singleton()->addCss(<<<'EOD'

.activation-wizard .wizard-steps {
    margin-bottom: 2rem;
}

.activation-wizard .wizard-content {
    min-height: 400px;
}

.activation-wizard .card {
    transition: all 0.3s ease;
}

.activation-wizard .card:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
    transform: translateY(-2px);
}

.activation-wizard .card.border-primary {
    border-width: 2px !important;
}

.activation-wizard .wizard-navigation {
    padding-top: 1rem;
    border-top: 1px solid #dee2e6;
}

EOD);

// Show completion page or wizard
// Load runtemplateId from session if not set (when accessing completion/finish page directly)
if (($step === 'complete' || $step === 'finish') && !isset($runtemplateId)) {
    if (isset($_SESSION['activation_wizard']['runtemplate_id'])) {
        $runtemplateId = (int) $_SESSION['activation_wizard']['runtemplate_id'];
        $step = 'complete'; // Set to complete to show summary
    } else {
        // No wizard data in session - redirect to start or show message
        $noDataMessage = new \Ease\TWB4\Alert(
            'warning',
            '⚠️ '._('No activation wizard data found. Please start a new activation.').'<br>'.
            '<a href="activation-wizard.php?reset=1" class="btn btn-primary mt-2">'._('Start New Activation').'</a>',
        );
        WebPage::singleton()->container->addItem($noDataMessage);
        WebPage::singleton()->addItem(new PageBottom());
        WebPage::singleton()->draw();

        exit;
    }
}

if ($step === 'complete' && isset($runtemplateId)) {
    $runTemplate = new \MultiFlexi\RunTemplate($runtemplateId);
    $app = new \MultiFlexi\Application($runTemplate->getDataValue('app_id'));
    $company = new \MultiFlexi\Company($runTemplate->getDataValue('company_id'));

    $completionCard = new \Ease\TWB4\Card(
        '🎉 '._('Activation Complete'),
    );

    $completionCard->addTagClass('text-white bg-success');

    $completionCard->addItem(new \Ease\Html\H4Tag(_('RunTemplate Summary')));

    // Create summary table
    $summaryTable = new \Ease\Html\TableTag(null, ['class' => 'table table-bordered']);
    $summaryTable->addRowColumns([
        new \Ease\Html\StrongTag(_('RunTemplate ID')),
        '#'.$runTemplate->getMyKey(),
    ]);
    $summaryTable->addRowColumns([
        new \Ease\Html\StrongTag(_('Name')),
        $runTemplate->getRecordName(),
    ]);
    $summaryTable->addRowColumns([
        new \Ease\Html\StrongTag(_('Application')),
        new \Ease\Html\SpanTag([
            $app->getDataValue('uuid') ? new \Ease\Html\ImgTag('appimage.php?uuid='.$app->getDataValue('uuid'), $app->getRecordName(), ['height' => '20', 'style' => 'margin-right: 5px;']) : '',
            $app->getRecordName(),
        ]),
    ]);
    $summaryTable->addRowColumns([
        new \Ease\Html\StrongTag(_('Company')),
        new \Ease\Html\SpanTag([
            $company->getDataValue('logo') ? new \Ease\Html\ImgTag($company->getDataValue('logo'), $company->getRecordName(), ['height' => '20', 'style' => 'margin-right: 5px;']) : '',
            $company->getRecordName(),
        ]),
    ]);
    $summaryTable->addRowColumns([
        new \Ease\Html\StrongTag(_('Interval')),
        \MultiFlexi\RunTemplate::codeToInterval($runTemplate->getDataValue('interv')),
    ]);

    // Show assigned credentials
    $credentials = $runTemplate->getAssignedCredentials();

    if (!empty($credentials)) {
        $credList = new \Ease\Html\UlTag();

        foreach ($credentials as $cred) {
            $credObj = new \MultiFlexi\Credential($cred['credential_id']);
            $credType = $credObj->getCredentialType();
            $credList->addItem(new \Ease\Html\LiTag([
                new \Ease\Html\ImgTag('images/'.$credType->getLogo(), $credType->getRecordName(), ['height' => '16', 'style' => 'margin-right: 5px;']),
                $credObj->getRecordName().' ('.$credType->getRecordName().')',
            ]));
        }

        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Credentials')),
            $credList,
        ]);
    }

    // Show actions
    $successActions = $runTemplate->getDataValue('success') ? unserialize($runTemplate->getDataValue('success')) : [];
    $failActions = $runTemplate->getDataValue('fail') ? unserialize($runTemplate->getDataValue('fail')) : [];

    $actionsEnabled = [];

    foreach ($successActions as $action => $enabled) {
        if ($enabled) {
            $actionsEnabled[] = '✅ '.$action.' ('._('on success').')';
        }
    }

    foreach ($failActions as $action => $enabled) {
        if ($enabled) {
            $actionsEnabled[] = '❌ '.$action.' ('._('on failure').')';
        }
    }

    if (!empty($actionsEnabled)) {
        $actionsList = new \Ease\Html\UlTag();

        foreach ($actionsEnabled as $actionText) {
            $actionsList->addItem(new \Ease\Html\LiTag($actionText));
        }

        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Actions')),
            $actionsList,
        ]);
    }

    $completionCard->addItem($summaryTable);

    $completionCard->addItem(new \Ease\Html\HrTag());
    $completionCard->addItem(new \Ease\Html\PTag(
        '🚀 '._('Your RunTemplate has been successfully created and configured!'),
    ));

    $buttonRow = new \Ease\TWB4\Row();
    $buttonRow->addColumn(
        4,
        new \Ease\TWB4\LinkButton(
            'runtemplate.php?id='.$runtemplateId,
            '⚗️ '._('View RunTemplate'),
            'primary btn-lg btn-block',
        ),
    );
    $buttonRow->addColumn(
        4,
        new \Ease\TWB4\LinkButton(
            'runtemplates.php',
            '📋 '._('All RunTemplates'),
            'secondary btn-lg btn-block',
        ),
    );
    $buttonRow->addColumn(
        4,
        new \Ease\TWB4\LinkButton(
            'activation-wizard.php?reset=1',
            '🌟 '._('New Activation'),
            'success btn-lg btn-block',
        ),
    );

    $completionCard->addItem($buttonRow);

    WebPage::singleton()->container->addItem($completionCard);
} else {
    // Convert step to int for wizard (finish/complete are handled above)
    $wizardStep = \is_int($step) ? $step : 1;
    $wizard = new ActivationWizard($wizardStep);
    WebPage::singleton()->container->addItem($wizard);
}

WebPage::singleton()->addItem(new PageBottom());
WebPage::singleton()->draw();
