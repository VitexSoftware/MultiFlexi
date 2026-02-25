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
 * Description of CustomAppConfigForm.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class RuntemplateConfigForm extends EngineForm
{
    public function __construct(\MultiFlexi\RunTemplate $engine)
    {
        parent::__construct($engine, null, ['method' => 'post', 'action' => 'runtemplate.php', 'enctype' => 'multipart/form-data']);

        $defaults = $engine->getAppEnvironment();
        $appRequirements = $engine->getApplication()->getRequirements();
        $customized = $engine->getRuntemplateEnvironment();

        $fieldsOf = [];
        $fieldSource = [];
        $credSource = [];

        $credentialProvidersAvailable = \MultiFlexi\Requirement::getCredentialProviders();
        $credentialTypesAvailable = \MultiFlexi\Requirement::getCredentialTypes($engine->getCompany());
        $credentialsAvailable = \MultiFlexi\Requirement::getCredentials($engine->getCompany());
        $credentialsAssigned = $engine->getAssignedCredentials();

        $credData = [];

        $this->addCSS(<<<'CSS'
            .runtemplate-config-form .form-group { margin-bottom: 0.75rem; padding: 0.5rem; border-radius: 4px; transition: background-color 0.2s; }
            .runtemplate-config-form .form-group:hover { background-color: #f8f9fa; }
            .runtemplate-config-form label { font-size: 0.9rem; margin-bottom: 0.2rem; display: block; }
            .runtemplate-config-form .form-control-sm { height: calc(1.5em + 0.5rem + 2px); padding: 0.25rem 0.5rem; font-size: 0.875rem; }
            .required-field { border-left: 3px solid #dc3545 !important; }
            .secret-field { border-left: 3px solid #343a40 !important; }
            .expiring-field { border-left: 3px solid #ffc107 !important; }
            .required-field.secret-field { border-left: 3px solid #dc3545 !important; border-right: 3px solid #343a40 !important; }
            .required-field.expiring-field { border-left: 3px solid #dc3545 !important; border-right: 3px solid #ffc107 !important; }
            .field-flags { display: inline; margin-left: 0.4rem; }
            .field-flags .badge { font-size: 0.7rem; margin-left: 0.15rem; vertical-align: middle; }
CSS);
        $this->addTagClass('runtemplate-config-form');

        $this->addItem(new RuntemplateRequirementsChoser($engine));

        $appFields = \MultiFlexi\Conffield::getAppConfigs($engine->getApplication());
        $runTemplateFields = $engine->getEnvironment();

        $appFields->takeValues($customized);

        foreach ($appFields as $fieldName => $field) {
            $inputCaption = new \Ease\Html\StrongTag($fieldName);

            if ($field->getType() === 'bool') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, $field->getValue() === 'true' ? true : false, 'true', ['data-size' => 'small']));
            } elseif ($field->isMultiLine()) {
                $input = new \Ease\Html\TextareaTag($fieldName, $field->getValue(), ['class' => 'form-control form-control-sm', 'rows' => 4]);
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $field->getValue(), ['type' => $field->getType(), 'class' => 'form-control form-control-sm']);
            }

            $runTemplateField = $runTemplateFields->getFieldByCode($fieldName);

            if ($runTemplateField) { // Filed by Credential
                $runTemplateFieldSource = $runTemplateField->getSource();

                if (\Ease\Euri::isValid($runTemplateFieldSource)) {
                    $credential = \Ease\Euri::toObject($runTemplateFieldSource);

                    if ($credential && (\Ease\Euri::getClass($runTemplateFieldSource) === 'MultiFlexi\\Credential')) {
                        $credentialType = $credential->getCredentialType();

                        $credentialLink = new \Ease\Html\ATag('credential.php?id='.$credential->getMyKey(), new \Ease\Html\SmallTag($credential->getRecordName()));

                        $formIcon = new \Ease\Html\ImgTag('images/'.$runTemplateField->getLogo(), (string) $credentialType->getRecordName(), ['height' => 20, 'title' => $credentialType->getRecordName()]);

                        $credentialTypeLink = new \Ease\Html\ATag('credentialtype.php?id='.$credentialType->getMyKey(), $formIcon);

                        $inputCaption = new \Ease\Html\SpanTag([$credentialTypeLink, new \Ease\Html\StrongTag($fieldName), '&nbsp;', $credentialLink]);
                        $input->setTagProperty('disabled', '1');

                        $input->setValue($credential->getDataValue($fieldName));
                        $field->setDescription($credentialType->getFields()->getField($fieldName)->getDescription());
                    }
                }

                $formGroup = $this->addInput($input, $inputCaption, $runTemplateField->getValue(), $field->getDescription());
            } else { // Simple Fields
                $formGroup = $this->addInput($input, $fieldName, $field->getDefaultValue(), $field->getDescription());
            }

            $flags = new \Ease\Html\SpanTag(null, ['class' => 'field-flags']);

            if ($field->isRequired()) {
                $formGroup->addTagClass('required-field');
                $flags->addItem(new \Ease\TWB4\Badge('danger', _('required')));
            }

            if ($field->isSecret()) {
                $formGroup->addTagClass('secret-field');
                $flags->addItem(new \Ease\TWB4\Badge('dark', '🔒 ' . _('secret')));
            }

            if ($field->isExpiring()) {
                $formGroup->addTagClass('expiring-field');
                $flags->addItem(new \Ease\TWB4\Badge('warning', '⏳ ' . _('expiring')));
            }

            if ($field->isMultiLine()) {
                $flags->addItem(new \Ease\TWB4\Badge('info', _('multiline')));
            }

            if (!empty($flags->pageParts)) {
                $formGroup->addItem($flags);
            }

            $hint = $field->getHint();

            if (!empty($hint)) {
                $formGroup->addItem(new \Ease\Html\SmallTag($hint, ['class' => 'form-text text-muted']));
            }
        }

        // $this->addItem( new RuntemplateTopicsChooser('topics', $engine)); //TODO

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));

        $saveRow = new \Ease\TWB4\Row();
        $saveColumn = $saveRow->addColumn(8, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
        $saveRow->addColumn(4, new \Ease\TWB4\LinkButton('actions.php?id='.$engine->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));

        $appSetupCommand = $engine->getApplication()->getDataValue('setup');

        if (!empty($appSetupCommand)) {
            $saveColumn->addItem(new \Ease\TWB4\Alert('info', 'ℹ️&nbsp;'._('After saving configuration, the following setup command will be executed:').'<br><code>'.htmlspecialchars((string) $appSetupCommand, \ENT_QUOTES | \ENT_HTML5, 'UTF-8').'</code>'));
        }

        $this->addItem($saveRow);
    }
}
