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

/**
 * Activation Wizard Component.
 *
 * Multi-step wizard for activating applications in companies.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class ActivationWizard extends \Ease\Html\DivTag
{
    /**
     * Current wizard step.
     */
    private int $currentStep;

    /**
     * Total wizard steps.
     */
    private int $totalSteps = 7;

    /**
     * Wizard data stored in session.
     */
    private array $wizardData;

    /**
     * Constructor.
     *
     * @param int $step Current step number (1-4)
     */
    public function __construct(int $step = 1)
    {
        parent::__construct(null, ['class' => 'activation-wizard']);
        $this->currentStep = max(1, min($step, $this->totalSteps));
        $this->initWizardData();
    }

    /**
     * Render wizard.
     */
    public function afterAdd(): void
    {
        $this->addItem($this->renderStepIndicator());
        $this->addItem($this->renderStepContent());
        $this->addItem($this->renderNavigation());
    }

    /**
     * Get wizard data.
     */
    public function getWizardData(): array
    {
        return $this->wizardData;
    }

    /**
     * Update wizard data.
     */
    public static function updateWizardData(array $data): void
    {
        if (!isset($_SESSION['activation_wizard'])) {
            $_SESSION['activation_wizard'] = [];
        }

        $_SESSION['activation_wizard'] = array_merge($_SESSION['activation_wizard'], $data);
    }

    /**
     * Clear wizard data.
     */
    public static function clearWizardData(): void
    {
        unset($_SESSION['activation_wizard']);
    }

    /**
     * Initialize wizard data from session.
     */
    private function initWizardData(): void
    {
        if (!isset($_SESSION['activation_wizard'])) {
            $_SESSION['activation_wizard'] = [
                'company_id' => null,
                'app_id' => null,
                'runtemplate_name' => null,
                'runtemplate_id' => null,
                'configuration' => [],
            ];
        }

        $this->wizardData = $_SESSION['activation_wizard'];
    }

    /**
     * Render step indicator.
     */
    private function renderStepIndicator(): \Ease\Html\DivTag
    {
        $stepIndicator = new \Ease\Html\DivTag(null, ['class' => 'wizard-steps mb-4']);
        $steps = [
            1 => _('Choose Company'),
            2 => _('Choose Application'),
            3 => _('Create RunTemplate'),
            4 => _('Assign Credentials'),
            5 => _('Configure'),
            6 => _('Actions'),
            7 => _('Summary'),
        ];

        // Add selected company logo
        if (!empty($this->wizardData['company_id'])) {
            $company = new \MultiFlexi\Company($this->wizardData['company_id']);
            $logo = $company->getDataValue('logo');

            if ($logo) {
                $steps[1] .= ' <img src="'.$logo.'" style="height: 20px; margin-left: 5px;" />';
            }
        }

        // Add selected application logo
        if (!empty($this->wizardData['app_id'])) {
            $app = new \MultiFlexi\Application($this->wizardData['app_id']);
            $uuid = $app->getDataValue('uuid');

            if ($uuid) {
                $steps[2] .= ' <img src="appimage.php?uuid='.$uuid.'" style="height: 20px; margin-left: 5px;" />';
            }
        }

        // Add RunTemplate ID - load actual ID from database
        if (!empty($this->wizardData['runtemplate_id'])) {
            $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
            $actualId = $runTemplate->getMyKey();

            if ($actualId) {
                $steps[3] .= ' ⚗️ #'.$actualId;
            }
        }

        $stepList = new \Ease\Html\UlTag(null, ['class' => 'nav nav-pills nav-fill']);

        foreach ($steps as $num => $label) {
            $itemClass = ['nav-item'];
            $linkClass = ['nav-link'];

            if ($num === $this->currentStep) {
                $linkClass[] = 'active';
            } elseif ($num < $this->currentStep) {
                $linkClass[] = 'text-success';
            } else {
                $linkClass[] = 'disabled';
            }

            $stepNumber = new \Ease\Html\SpanTag($num, ['class' => 'badge badge-pill badge-light mr-2']);
            $link = new \Ease\Html\ATag('#', [$stepNumber, $label], ['class' => implode(' ', $linkClass)]);

            $stepList->addItem(new \Ease\Html\LiTag($link, ['class' => implode(' ', $itemClass)]));
        }

        $stepIndicator->addItem($stepList);

        return $stepIndicator;
    }

    /**
     * Render current step content.
     */
    private function renderStepContent(): \Ease\Html\DivTag
    {
        $content = new \Ease\Html\DivTag(null, ['class' => 'wizard-content card']);
        $cardBody = $content->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

        switch ($this->currentStep) {
            case 1:
                $cardBody->addItem($this->renderCompanySelection());

                break;
            case 2:
                $cardBody->addItem($this->renderApplicationSelection());

                break;
            case 3:
                $cardBody->addItem($this->renderRunTemplateCreation());

                break;
            case 4:
                $cardBody->addItem($this->renderCredentialSelection());

                break;
            case 5:
                $cardBody->addItem($this->renderConfiguration());

                break;
            case 6:
                $cardBody->addItem($this->renderActions());

                break;
            case 7:
                $cardBody->addItem($this->renderSummary());

                break;
        }

        return $content;
    }

    /**
     * Render company selection step.
     */
    private function renderCompanySelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();
        $container->addItem(new \Ease\Html\H3Tag(_('Select Company')));
        $container->addItem(new \Ease\Html\PTag(_('Choose the company where you want to activate the application.')));

        $company = new \MultiFlexi\Company();
        $companies = $company->listingQuery()->orderBy('name')->fetchAll();

        if (empty($companies)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No companies found. Please create a company first.')));
            $container->addItem(new \Ease\TWB4\LinkButton('companysetup.php', _('Create Company'), 'primary'));

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=2', 'id' => 'wizardForm']);

        $companyCards = new \Ease\TWB4\Row();

        foreach ($companies as $companyData) {
            $isSelected = ($this->wizardData['company_id'] ?? null) === $companyData['id'];
            $cardClass = $isSelected ? 'border-primary bg-light' : '';

            $card = new \Ease\Html\DivTag(null, ['class' => 'card mb-3 '.$cardClass, 'style' => 'cursor: pointer;']);
            $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

            if (!empty($companyData['logo'])) {
                $cardBody->addItem(new \Ease\Html\ImgTag($companyData['logo'], $companyData['name'], ['class' => 'img-fluid mb-2', 'style' => 'max-height: 60px;']));
            }

            $cardBody->addItem(new \Ease\Html\H5Tag($companyData['name'], ['class' => 'card-title']));

            if (!empty($companyData['ic'])) {
                $cardBody->addItem(new \Ease\Html\PTag(_('ID').': '.$companyData['ic'], ['class' => 'card-text small']));
            }

            $radio = new \Ease\Html\InputTag('company_id', $companyData['id'], ['type' => 'radio', 'required' => 'required']);

            if ($isSelected) {
                $radio->setTagProperty('checked', 'checked');
            }

            $cardBody->addItem($radio);
            $cardBody->addItem(' '._('Select this company'));

            $companyCards->addColumn(4, $card);

            // JavaScript will be added once after the loop
        }

        $form->addItem($companyCards);
        $container->addItem($form);

        // Add JavaScript to make company cards clickable
        WebPage::singleton()->addJavaScript(
            <<<'EOD'
document.querySelectorAll('#wizardForm .card').forEach(function(card) {
                card.addEventListener('click', function() {
                    var radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        document.querySelectorAll('#wizardForm .card').forEach(c => {
                            c.classList.remove('border-primary', 'bg-light');
                        });
                        this.classList.add('border-primary', 'bg-light');
                    }
                });
            });
EOD,
            null,
            true,
        );

        return $container;
    }

    /**
     * Render application selection step.
     */
    private function renderApplicationSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('No company selected. Please go back to step 1.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $container->addItem(new \Ease\Html\H3Tag(_('Select Application for').' '.$company->getRecordName()));
        $container->addItem(new \Ease\Html\PTag(_('Choose an application to activate. The application will be assigned to the selected company.')));

        $app = new \MultiFlexi\Application();
        $applications = $app->listingQuery()->where('enabled', true)->orderBy('name')->fetchAll();

        if (empty($applications)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No applications available.')));

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=3', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));

        $appCards = new \Ease\TWB4\Row();

        foreach ($applications as $appData) {
            $isSelected = ($this->wizardData['app_id'] ?? null) === $appData['id'];
            $cardClass = $isSelected ? 'border-primary bg-light' : '';

            $card = new \Ease\Html\DivTag(null, ['class' => 'card mb-3 h-100 '.$cardClass, 'style' => 'cursor: pointer;']);
            $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']));

            // Show application logo/image using AppLogo component
            if (!empty($appData['uuid'])) {
                $appLogo = new \Ease\Html\ImgTag('appimage.php?uuid='.$appData['uuid'], $appData['name'], ['class' => 'img-fluid mb-3', 'style' => 'max-height: 80px; max-width: 100%;']);
                $cardBody->addItem($appLogo);
            } else {
                // Fallback icon if no uuid/image
                $cardBody->addItem(new \Ease\Html\DivTag('🧩', ['style' => 'font-size: 60px; margin-bottom: 1rem;']));
            }

            $cardBody->addItem(new \Ease\Html\H5Tag($appData['name'], ['class' => 'card-title']));

            if (!empty($appData['description'])) {
                $cardBody->addItem(new \Ease\Html\PTag($appData['description'], ['class' => 'card-text small text-muted']));
            }

            $radio = new \Ease\Html\InputTag('app_id', $appData['id'], ['type' => 'radio', 'required' => 'required']);

            if ($isSelected) {
                $radio->setTagProperty('checked', 'checked');
            }

            $cardBody->addItem(new \Ease\Html\DivTag([$radio, ' ', _('Select this application')], ['class' => 'mt-3']));

            $appCards->addColumn(4, $card);
        }

        $form->addItem($appCards);
        $container->addItem($form);

        // Add JavaScript to make app cards clickable
        WebPage::singleton()->addJavaScript(
            <<<'EOD'
document.querySelectorAll('.wizard-content .card').forEach(function(card) {
                card.addEventListener('click', function() {
                    var radio = this.querySelector('input[type="radio"]');
                    if (radio) {
                        radio.checked = true;
                        document.querySelectorAll('.wizard-content .card').forEach(c => {
                            c.classList.remove('border-primary', 'bg-light');
                        });
                        this.classList.add('border-primary', 'bg-light');
                    }
                });
            });
EOD,
            null,
            true,
        );

        return $container;
    }

    /**
     * Render RunTemplate creation step.
     */
    private function renderRunTemplateCreation(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id']) || empty($this->wizardData['app_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('Missing company or application. Please complete previous steps.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Create RunTemplate')));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Creating RunTemplate for %s in %s'), $app->getRecordName(), $company->getRecordName())));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=4', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));
        $form->addItem(new \Ease\Html\InputHiddenTag('app_id', (string) $this->wizardData['app_id']));

        // Default RunTemplate name: "Company / Application"
        $defaultName = $company->getRecordName().' / '.$app->getRecordName();
        $nameInput = new \Ease\Html\InputTextTag('runtemplate_name', $this->wizardData['runtemplate_name'] ?? $defaultName, ['class' => 'form-control', 'required' => 'required', 'placeholder' => _('RunTemplate name')]);
        $form->addItem(new \Ease\TWB4\FormGroup(_('RunTemplate Name'), $nameInput, '', _('Descriptive name for this configuration')));

        $intervalSelect = new IntervalChooser('interv', 'n', ['class' => 'form-control']);
        $form->addItem(new \Ease\TWB4\FormGroup(_('Schedule Interval'), $intervalSelect, '', _('How often should this run?')));

        $container->addItem($form);

        return $container;
    }

    /**
     * Render credential selection step.
     */
    private function renderCredentialSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Assign Credentials')));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Select credentials for %s'), $app->getRecordName())));

        // Get application requirements
        $requirements = $app->getRequirements();

        if (empty($requirements)) {
            $container->addItem(new \Ease\TWB4\Alert('info', _('This application does not require any credentials.')));
            // Auto-proceed button
            $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=5', 'id' => 'wizardForm']);
            $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));
            $container->addItem($form);

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=5', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        $credentialType = new \MultiFlexi\CredentialType();
        $credential = new \MultiFlexi\Credential();

        foreach ($requirements as $requirement) {
            $form->addItem(new \Ease\Html\H4Tag($requirement));

            // Find credential type by class name
            $credType = $credentialType->listingQuery()
                ->where('class', $requirement)
                ->fetch();

            if (!$credType) {
                $alert = new \Ease\TWB4\Alert('warning', sprintf(_('Credential type %s not found.'), $requirement));
                $alert->addItem(new \Ease\TWB4\LinkButton('credentialtype.php?class='.$requirement, _('Create Credential Type'), 'primary btn-sm'));
                $form->addItem($alert);

                continue;
            }

            // Get company credentials of this type
            $companyCredentials = $credential->listingQuery()
                ->where('company_id', $company->getMyKey())
                ->where('credential_type_id', $credType['id'])
                ->fetchAll();

            if (empty($companyCredentials)) {
                $alert = new \Ease\TWB4\Alert('warning', sprintf(_('No credentials found for %s.'), $requirement));
                $alert->addItem(new \Ease\TWB4\LinkButton('credential.php?company_id='.$company->getMyKey().'&credential_type_id='.$credType['id'], _('Create Credential'), 'primary btn-sm'));
                $form->addItem($alert);

                continue;
            }

            // Create select for credentials
            $select = new \Ease\Html\SelectTag('credential['.$requirement.']');
            $select->addTagClass('form-control');
            $select->addItem(new \Ease\Html\OptionTag(_('-- Select Credential --'), ''));

            foreach ($companyCredentials as $cred) {
                $select->addItem(new \Ease\Html\OptionTag($cred['name'], (string) $cred['id']));
            }

            $formGroup = new \Ease\TWB4\FormGroup(
                sprintf(_('Credential for %s'), $requirement),
                $select,
                '',
                sprintf(_('Select which %s credential to use'), $requirement),
            );
            $form->addItem($formGroup);
        }

        $container->addItem($form);

        return $container;
    }

    /**
     * Render configuration step.
     */
    private function renderConfiguration(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Configure').' '.$runTemplate->getRecordName()));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=6', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        // Get application environment fields and runtemplate fields
        $confField = new \MultiFlexi\Conffield();
        $appConfigs = $confField->getAppConfigs($app);
        $runTemplateFields = $runTemplate->getEnvironment();
        $customized = $runTemplate->getRuntemplateEnvironment();
        $appConfigs->addFields($customized);

        if (empty($appConfigs->getFields())) {
            $container->addItem(new \Ease\TWB4\Alert('info', _('This application does not require any configuration.')));
        } else {
            foreach ($appConfigs as $fieldName => $field) {
                $runTemplateField = $runTemplateFields->getFieldByCode($fieldName);

                if ($runTemplateField) {
                    // Field is filled by credential
                    $runTemplateFieldSource = $runTemplateField->getSource();

                    if (\Ease\Functions::isSerialized($runTemplateFieldSource)) {
                        $credential = unserialize($runTemplateFieldSource);

                        if ($credential) {
                            $credentialType = $credential->getCredentialType();
                            $credentialLink = new \Ease\Html\ATag('credential.php?id='.$credential->getMyKey(), new \Ease\Html\SmallTag($credential->getRecordName()));
                            $formIcon = new \Ease\Html\ImgTag('images/'.$runTemplateField->getLogo(), (string) $credentialType->getRecordName(), ['height' => 20, 'title' => $credentialType->getRecordName()]);
                            $credentialTypeLink = new \Ease\Html\ATag('credentialtype.php?id='.$credentialType->getMyKey(), $formIcon);
                            $inputCaption = new \Ease\Html\SpanTag([$credentialTypeLink, new \Ease\Html\StrongTag($fieldName), '&nbsp;', $credentialLink]);

                            $input = $this->createConfigInput($field, $fieldName, $runTemplateField->getValue());
                            $input->setTagProperty('disabled', '1');
                            $form->addItem(new \Ease\TWB4\FormGroup($inputCaption, $input, $field->getDescription(), ''));
                        } else {
                            $input = $this->createConfigInput($field, $fieldName);
                            $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                        }
                    } else {
                        $input = $this->createConfigInput($field, $fieldName);
                        $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                    }
                } else {
                    // Simple field without credential
                    $input = $this->createConfigInput($field, $fieldName);
                    $form->addItem(new \Ease\TWB4\FormGroup($fieldName, $input, $field->getDescription(), ''));
                }
            }
        }

        $container->addItem($form);

        return $container;
    }

    /**
     * Create configuration input based on field type.
     *
     * @param \MultiFlexi\ConfigField $field
     * @param string                  $fieldName
     * @param mixed                   $overrideValue Optional value to override default
     *
     * @return \Ease\Html\Tag
     */
    private function createConfigInput($field, $fieldName, $overrideValue = null)
    {
        $type = $field->getType();
        $value = $overrideValue ?? ($this->wizardData['configuration'][$fieldName] ?? $field->getValue());

        switch ($type) {
            case 'bool':
            case 'boolean':
                $input = new \Ease\Html\InputTag($fieldName, '1', ['type' => 'checkbox', 'class' => 'form-check-input']);

                if ($value) {
                    $input->setTagProperty('checked', 'checked');
                }

                return new \Ease\Html\DivTag($input, ['class' => 'form-check']);
            case 'password':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'password', 'class' => 'form-control']);
            case 'int':
            case 'integer':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'number', 'class' => 'form-control']);
            case 'file-path':
                return new \Ease\Html\InputTag($fieldName, $value, ['type' => 'file', 'class' => 'form-control-file']);

            default:
                return new \Ease\Html\InputTextTag($fieldName, $value, ['class' => 'form-control']);
        }
    }

    /**
     * Render navigation buttons.
     */
    private function renderNavigation(): \Ease\Html\DivTag
    {
        $nav = new \Ease\Html\DivTag(null, ['class' => 'wizard-navigation mt-4 d-flex justify-content-between']);

        // Previous button
        if ($this->currentStep > 1) {
            $prevButton = new \Ease\TWB4\LinkButton('activation-wizard.php?step='.($this->currentStep - 1), _('Previous'), 'secondary');
            $nav->addItem($prevButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Middle section - Create Company button for step 1, Create Application button for step 2
        if ($this->currentStep === 1) {
            $createCompanyButton = new \Ease\TWB4\LinkButton('companysetup.php', '➕ '._('Create Company'), 'info');
            $nav->addItem($createCompanyButton);
        } elseif ($this->currentStep === 2) {
            $createApplicationButton = new \Ease\TWB4\LinkButton('app.php', '🧩 '._('Create Application'), 'info');
            $nav->addItem($createApplicationButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Next/Finish button
        if ($this->currentStep < $this->totalSteps - 1) {
            $nextButton = new \Ease\Html\ButtonTag(_('Next'), ['class' => 'btn btn-primary', 'type' => 'submit', 'form' => 'wizardForm']);
            $nav->addItem($nextButton);
        } elseif ($this->currentStep === $this->totalSteps - 1) {
            // Step 6 (Actions) - button to save and go to summary
            $finishButton = new \Ease\Html\ButtonTag(_('Finish & View Summary'), ['class' => 'btn btn-success', 'type' => 'submit', 'form' => 'wizardForm']);
            $nav->addItem($finishButton);
        } elseif ($this->currentStep === $this->totalSteps) {
            // Step 7 (Summary) - no next button, only links in the content
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        return $nav;
    }

    /**
     * Render summary/completion step.
     */
    private function renderSummary(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        $container->addItem(new \Ease\Html\H3Tag('🎉 '._('Activation Complete')));
        $container->addItem(new \Ease\Html\PTag('🚀 '._('Your RunTemplate has been successfully created and configured!')));
        $container->addItem(new \Ease\Html\HrTag());
        $container->addItem(new \Ease\Html\H4Tag(_('RunTemplate Summary')));

        // Create summary table
        $summaryTable = new \Ease\Html\TableTag(null, ['class' => 'table table-bordered']);

        // Get RunTemplate ID - use actual ID from database object
        $runtemplateId = $runTemplate->getMyKey() ?: $this->wizardData['runtemplate_id'];
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('RunTemplate ID')),
            '#'.$runtemplateId,
        ]);

        // Get RunTemplate name - use the object's name or fallback to wizard data
        $runtemplateName = $runTemplate->getRecordName() ?: ($this->wizardData['runtemplate_name'] ?? _('Unknown'));
        $summaryTable->addRowColumns([
            new \Ease\Html\StrongTag(_('Name')),
            $runtemplateName,
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

        $container->addItem($summaryTable);
        $container->addItem(new \Ease\Html\HrTag());

        // Action buttons
        $buttonRow = new \Ease\TWB4\Row();
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'runtemplate.php?id='.$runtemplateId,
                '⚗️ '._('View RunTemplate'),
                'primary btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'schedule.php?app_id='.$app->getMyKey().'&company_id='.$company->getMyKey().'&runtemplate_id='.$runtemplateId,
                '📅 '._('Schedule'),
                'info btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'runtemplates.php',
                '📋 '._('All RunTemplates'),
                'secondary btn-lg btn-block',
            ),
        );
        $buttonRow->addColumn(
            3,
            new \Ease\TWB4\LinkButton(
                'activation-wizard.php?reset=1',
                '🌟 '._('New Activation'),
                'success btn-lg btn-block',
            ),
        );

        $container->addItem($buttonRow);

        return $container;
    }

    /**
     * Render actions configuration step.
     */
    private function renderActions(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['runtemplate_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('RunTemplate not created. Please complete previous steps.')));

            return $container;
        }

        $runTemplate = new \MultiFlexi\RunTemplate($this->wizardData['runtemplate_id']);
        $app = new \MultiFlexi\Application($this->wizardData['app_id']);
        $company = new \MultiFlexi\Company($this->wizardData['company_id']);

        // Add status message about created RunTemplate with actual ID
        $actualId = $runTemplate->getMyKey();

        if ($actualId) {
            $runTemplate->addStatusMessage(
                sprintf(
                    _('RunTemplate #%d created for application "%s" and company "%s"'),
                    $actualId,
                    $app->getRecordName(),
                    $company->getRecordName(),
                ),
                'success',
            );
        }

        $container->addItem(new \Ease\Html\H3Tag(_('Configure Actions').' '.$runTemplate->getRecordName()));
        $container->addItem(new \Ease\Html\PTag(_('Define what happens when the job succeeds or fails.')));

        $form = new SecureForm(['method' => 'POST', 'action' => 'activation-wizard.php?step=7', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('runtemplate_id', (string) $this->wizardData['runtemplate_id']));

        // Get existing actions if any
        $failActions = $runTemplate->getDataValue('fail') ? unserialize($runTemplate->getDataValue('fail')) : [];
        $successActions = $runTemplate->getDataValue('success') ? unserialize($runTemplate->getDataValue('success')) : [];

        // Create tabs for success and fail actions
        $actionsRow = new \Ease\TWB4\Tabs();
        $actionsRow->addTab(_('Success Actions'), new ActionsChooser('success', $app, $successActions), !empty($successActions));
        $actionsRow->addTab(_('Fail Actions'), new ActionsChooser('fail', $app, $failActions), !empty($failActions));

        $form->addItem($actionsRow);
        $container->addItem($form);

        return $container;
    }
}
