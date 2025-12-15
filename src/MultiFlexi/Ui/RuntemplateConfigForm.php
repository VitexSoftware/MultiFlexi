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

        $this->addItem(new RuntemplateRequirementsChoser($engine));

        $appFields = \MultiFlexi\Conffield::getAppConfigs($engine->getApplication());
        $runTemplateFields = $engine->getEnvironment();

        $appFields->addFields($customized);

        foreach ($appFields as $fieldName => $field) {
            $inputCaption = new \Ease\Html\StrongTag($fieldName);
            if ($field->getType() === 'bool') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, $field->getValue() === 'true' ? true : false, 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $field->getValue(), ['type' => $field->getType()]);
            }

            $runTemplateField = $runTemplateFields->getFieldByCode($fieldName);

            if ($runTemplateField) { // Filed by Credential
                $runTemplateFieldSource = $runTemplateField->getSource();

                if (\Ease\Functions::isSerialized($runTemplateFieldSource)) {
                    $credential = unserialize($runTemplateFieldSource);

                    if ($credential && (\Ease\Functions::baseClassName($credential) == 'Credential')) {
                        $credentialType = $credential->getCredentialType();

                        $credentialLink = new \Ease\Html\ATag('credential.php?id='.$credential->getMyKey(), new \Ease\Html\SmallTag($credential->getRecordName()));

                        $formIcon = new \Ease\Html\ImgTag('images/'.$runTemplateField->getLogo(), (string) $credentialType->getRecordName(), ['height' => 20, 'title' => $credentialType->getRecordName()]);

                        $credentialTypeLink = new \Ease\Html\ATag('credentialtype.php?id='.$credentialType->getMyKey(), $formIcon);

                        $inputCaption = new \Ease\Html\SpanTag([$credentialTypeLink, new \Ease\Html\StrongTag($fieldName), '&nbsp;', $credentialLink]);
                        $input->setTagProperty('disabled', '1');
                    }
                }
                $formGroup = $this->addInput($input, $inputCaption, $runTemplateField->getValue(), $field->getDescription());
            } else { // Simple Fields
                $formGroup = $this->addInput($input, $fieldName, $field->getDefaultValue(), $field->getDescription());
            }

            //            if ($field->isRequired()) {
            //                $formGroup->addTagClass('bg-danger');
            //            }
            //
            //            if ($field->getSource()) {
            //                $formGroup->addTagClass('bg-info');
            //            }
        }

        // $this->addItem( new RuntemplateTopicsChooser('topics', $engine)); //TODO

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));

        $saveRow = new \Ease\TWB4\Row();
        $saveColumn = $saveRow->addColumn(8, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
        $saveRow->addColumn(4, new \Ease\TWB4\LinkButton('actions.php?id='.$engine->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));

        $appSetupCommand = $engine->getApplication()->getDataValue('setup');

        if (!empty($appSetupCommand)) {
            $saveColumn->addItem(new \Ease\TWB4\Alert('info', 'ℹ️&nbsp;'._('After saving configuration, the following setup command will be executed:').'<br><code>'.$appSetupCommand.'</code>'));
        }

        $this->addItem($saveRow);
    }
}
