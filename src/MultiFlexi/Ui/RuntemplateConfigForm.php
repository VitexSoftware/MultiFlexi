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
 */
class RuntemplateConfigForm extends EngineForm
{
    private array $modulesEnv;

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

        $appFields = \MultiFlexi\Conffield::getAppConfigs($engine->getDataValue('app_id'));
        $runTemplateFields = $engine->getEnvironment();

        $appFields->addFields($customized);

        foreach ($appFields as $fieldName => $field) {
            if ($field->getType() === 'bool') {
                $input = new \Ease\Html\DivTag(new \Ease\TWB4\Widgets\Toggle($fieldName, $field->getValue() === 'true' ? true : false, 'true', []));
            } else {
                $input = new \Ease\Html\InputTag($fieldName, $field->getValue(), ['type' => $field->getType()]);
            }

            $runTemplateField = $runTemplateFields->getFieldByCode($fieldName);

            if ($runTemplateField) {
                if (\array_key_exists($fieldName, $credData)) {
                    $input->setTagProperty('disabled', '1');
                    $input->setValue($credData[$fieldName]);
                }

                $formIcon = new \Ease\Html\ImgTag($runTemplateField->getLogo(), $runTemplateField->getSource(), ['height' => 20, 'title' => $runTemplateField->getSource()]);

                if (\array_key_exists($fieldName, $credSource)) {
                    $fieldLink = new \Ease\Html\ATag('credential.php?id='.$credSource[$fieldName], $formIcon.'&nbsp;'.$fieldName);
                } else {
                    $fieldLink = $formIcon.'&nbsp;'.$fieldName;
                }

                $formGroup = $this->addInput($input, $fieldLink, $field->getDefaultValue(), $field->getDescription());
            } else {
                $formGroup = $this->addInput($input, $fieldName.'&nbsp;('.$field->getSource().')', $field->getDefaultValue(), $field->getDescription());
            }

            if ($field->isRequired()) {
                $formGroup->addTagClass('bg-danger');
            }

            if ($field->getSource()) {
                $formGroup->addTagClass('bg-info');
            }
        }

        // $this->addItem( new RuntemplateTopicsChooser('topics', $engine)); //TODO

        $this->addItem(new \Ease\Html\InputHiddenTag('app_id', $engine->getDataValue('app_id')));
        $this->addItem(new \Ease\Html\InputHiddenTag('company_id', $engine->getDataValue('company_id')));

        $saveRow = new \Ease\TWB4\Row();
        $saveRow->addColumn(8, new \Ease\TWB4\SubmitButton(_('Save'), 'success btn-lg btn-block'));
        $saveRow->addColumn(4, new \Ease\TWB4\LinkButton('actions.php?id='.$engine->getMyKey(), '🛠️&nbsp;'._('Actions'), 'secondary btn-lg btn-block'));
        $this->addItem($saveRow);
    }

    public static function allForms(): array
    {
        $formTypes = [];
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\Ui\Form');

        foreach (\Ease\Functions::classesInNamespace('MultiFlexi\Ui\Form') as $formAvailble) {
            $formClass = '\\MultiFlexi\\Ui\\Form\\'.$formAvailble;

            $formTypes[$formAvailble] = $formClass::name();
        }

        return $formTypes;
    }
}
