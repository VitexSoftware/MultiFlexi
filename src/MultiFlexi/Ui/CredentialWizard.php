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
 * Credential Wizard Component.
 *
 * Multi-step wizard for creating credentials through prototype->type->credential flow.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class CredentialWizard extends \Ease\Html\DivTag
{
    /**
     * Current wizard step.
     */
    private int $currentStep;

    /**
     * Total wizard steps.
     */
    private int $totalSteps = 4;

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
        parent::__construct(null, ['class' => 'credential-wizard']);
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
        if (!isset($_SESSION['credential_wizard'])) {
            $_SESSION['credential_wizard'] = [];
        }

        $_SESSION['credential_wizard'] = array_merge($_SESSION['credential_wizard'], $data);
    }

    /**
     * Clear wizard data.
     */
    public static function clearWizardData(): void
    {
        unset($_SESSION['credential_wizard']);
    }

    /**
     * Initialize wizard data from session.
     */
    private function initWizardData(): void
    {
        if (!isset($_SESSION['credential_wizard'])) {
            $_SESSION['credential_wizard'] = [
                'company_id' => null,
                'credential_prototype_id' => null,
                'credential_type_id' => null,
                'credential_id' => null,
            ];
        }

        $this->wizardData = $_SESSION['credential_wizard'];
    }

    /**
     * Render step indicator.
     */
    private function renderStepIndicator(): \Ease\Html\DivTag
    {
        $stepIndicator = new \Ease\Html\DivTag(null, ['class' => 'wizard-steps mb-4']);
        $steps = [
            1 => _('Choose Company'),
            2 => _('Choose Credential Prototype'),
            3 => _('Choose/Create Credential Type'),
            4 => _('Create Credential'),
        ];

        // Add selected company logo
        if (!empty($this->wizardData['company_id'])) {
            $company = new \MultiFlexi\Company($this->wizardData['company_id']);
            $logo = $company->getDataValue('logo');

            if ($logo) {
                $steps[1] .= ' <img src="'.$logo.'" style="height: 20px; margin-left: 5px;" />';
            }
        }

        // Add prototype name
        if (!empty($this->wizardData['credential_prototype_id'])) {
            $prototype = new \MultiFlexi\CredentialProtoType($this->wizardData['credential_prototype_id']);
            $steps[2] .= ' <span class="badge badge-info ml-2">'.$prototype->getRecordName().'</span>';
        }

        // Add credential type name
        if (!empty($this->wizardData['credential_type_id'])) {
            $credType = new \MultiFlexi\CredentialType($this->wizardData['credential_type_id']);
            $steps[3] .= ' <span class="badge badge-success ml-2">'.$credType->getRecordName().'</span>';
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
                $cardBody->addItem($this->renderPrototypeSelection());

                break;
            case 3:
                $cardBody->addItem($this->renderCredentialTypeSelection());

                break;
            case 4:
                $cardBody->addItem($this->renderCredentialCreation());

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
        $container->addItem(new \Ease\Html\PTag(_('Choose the company for which you want to create credentials.')));

        $company = new \MultiFlexi\Company();
        $companies = $company->listingQuery()->orderBy('name')->fetchAll();

        if (empty($companies)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No companies found. Please create a company first.')));
            $container->addItem(new \Ease\TWB4\LinkButton('companysetup.php', _('Create Company'), 'primary'));

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'credential-wizard.php?step=2', 'id' => 'wizardForm']);

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
     * Render credential prototype selection step.
     */
    private function renderPrototypeSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('No company selected. Please go back to step 1.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $container->addItem(new \Ease\Html\H3Tag(_('Select Credential Prototype for').' '.$company->getRecordName()));
        $container->addItem(new \Ease\Html\PTag(_('Choose a credential prototype that defines the type of credentials you want to create.')));

        $prototype = new \MultiFlexi\CredentialProtoType();
        $prototypes = $prototype->listingQuery()->orderBy('name')->fetchAll();

        if (empty($prototypes)) {
            $container->addItem(new \Ease\TWB4\Alert('warning', _('No credential prototypes found.')));
            $container->addItem(new \Ease\TWB4\LinkButton('credentialprototype.php', _('Create Credential Prototype'), 'primary', ['title' => _('Create new credential prototype'),'id' => 'createprototypewizardbutton']));

            return $container;
        }

        $form = new SecureForm(['method' => 'POST', 'action' => 'credential-wizard.php?step=3', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));

        $prototypeCards = new \Ease\TWB4\Row();

        foreach ($prototypes as $prototypeData) {
            $isSelected = ($this->wizardData['credential_prototype_id'] ?? null) === $prototypeData['id'];
            $cardClass = $isSelected ? 'border-primary bg-light' : '';

            $card = new \Ease\Html\DivTag(null, ['class' => 'card mb-3 h-100 '.$cardClass, 'style' => 'cursor: pointer;']);
            $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

            // Show prototype icon
            if ($prototypeData['logo']) {
                $cardBody->addItem(new \Ease\Html\DivTag(new \Ease\Html\ImgTag('images/'.$prototypeData['logo'], $prototypeData['name'], ['height' => '40px']), ['style' => 'font-size: 40px; margin-bottom: 1rem;']));
            } else {
                $cardBody->addItem(new \Ease\Html\DivTag('🔐', ['style' => 'font-size: 40px; margin-bottom: 1rem;']));
            }

            $cardBody->addItem(new \Ease\Html\H5Tag($prototypeData['name'], ['class' => 'card-title']));

            if (!empty($prototypeData['description'])) {
                $cardBody->addItem(new \Ease\Html\PTag($prototypeData['description'], ['class' => 'card-text small text-muted']));
            }

            $radio = new \Ease\Html\InputTag('credential_prototype_id', $prototypeData['id'], ['type' => 'radio', 'required' => 'required']);

            if ($isSelected) {
                $radio->setTagProperty('checked', 'checked');
            }

            $cardBody->addItem(new \Ease\Html\DivTag([$radio, ' ', _('Select this prototype')], ['class' => 'mt-3']));

            $prototypeCards->addColumn(4, $card);
        }

        $form->addItem($prototypeCards);
        $container->addItem($form);

        // Add JavaScript to make prototype cards clickable
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
     * Render credential type selection/creation step.
     */
    private function renderCredentialTypeSelection(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id']) || empty($this->wizardData['credential_prototype_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('Missing company or prototype. Please complete previous steps.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $prototype = new \MultiFlexi\CredentialProtoType($this->wizardData['credential_prototype_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Choose or Create Credential Type')));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Select existing credential type for %s or create a new one based on prototype %s'), $company->getRecordName(), $prototype->getRecordName())));

        $form = new SecureForm(['method' => 'POST', 'action' => 'credential-wizard.php?step=4', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));
        $form->addItem(new \Ease\Html\InputHiddenTag('credential_prototype_id', (string) $this->wizardData['credential_prototype_id']));

        // Get existing credential types for this company
        $credentialType = new \MultiFlexi\CredentialType();
        $existingTypes = $credentialType->listingQuery()
            ->where('company_id', $this->wizardData['company_id'])
            ->orderBy('name')
            ->fetchAll();

        if (!empty($existingTypes)) {
            $container->addItem(new \Ease\Html\H4Tag(_('Existing Credential Types')));
            $typeCards = new \Ease\TWB4\Row();

            foreach ($existingTypes as $typeData) {
                $isSelected = ($this->wizardData['credential_type_id'] ?? null) === $typeData['id'];
                $cardClass = $isSelected ? 'border-primary bg-light' : '';

                $card = new \Ease\Html\DivTag(null, ['class' => 'card mb-3 '.$cardClass, 'style' => 'cursor: pointer;']);
                $cardBody = $card->addItem(new \Ease\Html\DivTag(null, ['class' => 'card-body']));

                if (!empty($typeData['logo'])) {
                    $cardBody->addItem(new \Ease\Html\ImgTag('images/'.$typeData['logo'], $typeData['name'], ['height' => '40', 'class' => 'mb-2']));
                }

                $cardBody->addItem(new \Ease\Html\H5Tag($typeData['name'], ['class' => 'card-title']));

                $radio = new \Ease\Html\InputTag('credential_type_id', $typeData['id'], ['type' => 'radio', 'name' => 'credential_type_choice']);

                if ($isSelected) {
                    $radio->setTagProperty('checked', 'checked');
                }

                $cardBody->addItem($radio);
                $cardBody->addItem(' '._('Use this type'));

                $typeCards->addColumn(4, $card);
            }

            $form->addItem($typeCards);
            $form->addItem(new \Ease\Html\HrTag());
        }

        // Option to create new credential type
        $container->addItem(new \Ease\Html\H4Tag(_('Or Create New Credential Type')));

        $newTypeCard = new \Ease\TWB4\Card(_('Create New Type'));
        $newTypeCard->addTagClass('border-info');

        $newTypeNameInput = new \Ease\Html\InputTextTag('new_credential_type_name', '', ['class' => 'form-control', 'placeholder' => _('New credential type name')]);
        $newTypeCard->addItem(new \Ease\TWB4\FormGroup(_('Type Name'), $newTypeNameInput));

        $createNewRadio = new \Ease\Html\InputTag('credential_type_choice', 'new', ['type' => 'radio', 'name' => 'credential_type_choice', 'id' => 'create_new_radio']);
        $newTypeCard->addItem(new \Ease\Html\DivTag([$createNewRadio, ' ', _('Create new credential type')], ['class' => 'form-check']));

        $form->addItem($newTypeCard);

        $container->addItem($form);

        // Add JavaScript
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
     * Render credential creation step.
     */
    private function renderCredentialCreation(): \Ease\Html\DivTag
    {
        $container = new \Ease\Html\DivTag();

        if (empty($this->wizardData['company_id']) || empty($this->wizardData['credential_prototype_id']) || empty($this->wizardData['credential_type_id'])) {
            $container->addItem(new \Ease\TWB4\Alert('danger', _('Missing required data. Please complete previous steps.')));

            return $container;
        }

        $company = new \MultiFlexi\Company($this->wizardData['company_id']);
        $credentialType = new \MultiFlexi\CredentialType($this->wizardData['credential_type_id']);

        $container->addItem(new \Ease\Html\H3Tag(_('Create Credential')));
        $container->addItem(new \Ease\Html\PTag(sprintf(_('Create credential of type %s for %s'), $credentialType->getRecordName(), $company->getRecordName())));

        $form = new SecureForm(['method' => 'POST', 'action' => 'credential-wizard.php?step=complete', 'id' => 'wizardForm']);
        $form->addItem(new \Ease\Html\InputHiddenTag('company_id', (string) $this->wizardData['company_id']));
        $form->addItem(new \Ease\Html\InputHiddenTag('credential_type_id', (string) $this->wizardData['credential_type_id']));

        // Credential name
        $nameInput = new \Ease\Html\InputTextTag('name', '', ['class' => 'form-control', 'required' => 'required', 'placeholder' => _('Credential name')]);
        $form->addItem(new \Ease\TWB4\FormGroup(_('Credential Name'), $nameInput));

        // Get fields from credential type
        $fieldsSource = $credentialType->getFields();

        foreach ($fieldsSource->getFields() as $field) {
            $fieldName = $field->getCode();
            $fieldType = $field->getType();
            $required = $field->isRequired() ? 'required' : '';

            switch ($fieldType) {
                case 'password':
                    $input = new \Ease\Html\InputTag($fieldName, '', ['type' => 'password', 'class' => 'form-control', $required => $required]);

                    break;
                case 'text':
                    $input = new \Ease\Html\TextareaTag($fieldName, '', ['class' => 'form-control', $required => $required]);

                    break;

                default:
                    $input = new \Ease\Html\InputTextTag($fieldName, '', ['class' => 'form-control', $required => $required]);

                    break;
            }

            $form->addItem(new \Ease\TWB4\FormGroup($field->getName() ?: $fieldName, $input, '', $field->getDescription()));
        }

        $container->addItem($form);

        return $container;
    }

    /**
     * Render navigation buttons.
     */
    private function renderNavigation(): \Ease\Html\DivTag
    {
        $nav = new \Ease\Html\DivTag(null, ['class' => 'wizard-navigation mt-4 d-flex justify-content-between']);

        // Previous button
        if ($this->currentStep > 1) {
            $prevButton = new \Ease\TWB4\LinkButton('credential-wizard.php?step='.($this->currentStep - 1), _('Previous'), 'secondary', ['title' => _('Go to previous step'), 'id' => 'prevwizardcredentialbutton']);
            $nav->addItem($prevButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Middle section - Create Company button for step 1
        if ($this->currentStep === 1) {
            $createCompanyButton = new \Ease\TWB4\LinkButton('companysetup.php', '➕ '._('Create Company'), 'info');
            $nav->addItem($createCompanyButton);
        } elseif ($this->currentStep === 2) {
            $createPrototypeButton = new \Ease\TWB4\LinkButton('credentialprototype.php', '🔐 '._('Create Prototype'), 'info', ['title' => _('Create new credential prototype'),'id' => 'createprototypewizardbutton']);
            $nav->addItem($createPrototypeButton);
        } else {
            $nav->addItem(new \Ease\Html\DivTag()); // Empty div for spacing
        }

        // Next/Finish button
        if ($this->currentStep < $this->totalSteps) {
            $nextButton = new \Ease\Html\ButtonTag(_('Next'), ['class' => 'btn btn-primary', 'type' => 'submit', 'form' => 'wizardForm', 'title' => _('Go to next step'), 'id' => 'nextwizardcredentialbutton']);
            $nav->addItem($nextButton);
        } else {
            $finishButton = new \Ease\Html\ButtonTag(_('Create Credential'), ['class' => 'btn btn-success', 'type' => 'submit', 'form' => 'wizardForm', 'title' => _('Finish and create credential'),'id' => 'finishwizardcredentialbutton']);
            $nav->addItem($finishButton);
        }

        return $nav;
    }
}
