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
 * Class CredentialTypeForm.
 *
 * Handles the form for editing CredentialType.
 */
class CredentialTypeForm extends \Ease\TWB4\Form
{
    public \MultiFlexi\CredentialType $credType;

    public function __construct(\MultiFlexi\CredentialType $credtype, $formProperties = [])
    {
        $this->credType = $credtype;
        $credTypeRow1 = new \Ease\TWB4\Row();
        $companyId = $credtype->getDataValue('company_id');

        $logos['helper'] = new CredentialTypeLogo($credtype, ['style' => 'height: 200px'], ['class' => 'img-fluid']);

        if ($companyId) {
            $logos['company'] = new CompanyLogo(new \MultiFlexi\Company($companyId), ['class' => 'img-fluid']);
        }

        $credTypeRow1->addColumn(2, $logos);
        $credTypeRow1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $credtype->getRecordName())),
            new \Ease\TWB4\FormGroup(_('Company'), new CompanySelect('company_id', $companyId)),
            new \Ease\TWB4\FormGroup(_('Credential UUID'), new \Ease\Html\InputTextTag('uuid', $credtype->getDataValue('uuid'), ['disabled'])),
        ]);
        $helperCol = $credTypeRow1->addColumn(4, new \Ease\TWB4\FormGroup(_('Credential type Helper Class'), new CredentialTypeClassSelect('class', [], (string) $credtype->getDataValue('class'))));

        if ($credtype->getDataValue('class')) {
            $credtype->getHelper()->prepareConfigForm();

            $helperCol->addItem(new FieldsForm($credtype->getHelper()->fieldsInternal(), $credtype->getDataValue('class')));

            $provided = new \Ease\TWB4\Panel(new \Ease\Html\H4Tag(_('Fields provided')));

            $assigned = $credtype->getFields();

            foreach ($credtype->getHelper()->fieldsProvided() as $fieldProvided) {
                $fieldRow = new \Ease\TWB4\Row();

                $fieldRow->addColumn(4, $fieldProvided->getName().'<br>'.($fieldProvided->isRequired() ? _('Required') : _('Optional')).' '.$fieldProvided->getType());
                $fieldRow->addColumn(6, $fieldProvided->getDescription());

                $flags = new \Ease\Html\SpanTag();

                if (\is_object($assigned->getFieldByCode($fieldProvided->getCode()))) {
                    $flags->addItem(new \Ease\TWB4\LinkButton('#', '➕', 'disabled', ['title' => _('Already assigned')]));
                } else {
                    if ($fieldProvided->isRequired()) {
                        $credtype->addStatusMessage(sprintf(_('The %s field is required for %s'), $fieldProvided->getCode(), $credtype->getHelper()->name()), 'warning');
                    }

                    $flags->addItem(new \Ease\TWB4\LinkButton('?id='.$credtype->getMyKey().'&addField='.$fieldProvided->getCode(), '➕', 'success', ['title' => _('Add Field to ')]));
                }

                $fieldRow->addColumn(2, $flags);

                $provided->addItem($fieldRow);
            }

            $helperCol->addItem($provided);
        }

        $formContents[] = $credTypeRow1;

        $formContents[] = new \Ease\Html\DivTag($this->credTypeField('new', new \MultiFlexi\ConfigFieldWithHelper('', 'string', '', '')), ['class' => 'border border-primary rounded']);

        $fields = $credtype->getFields();

        foreach ($fields as $crTypeField) {
            $formContents[] = $this->credTypeField((string) $crTypeField->getMykey(), $crTypeField);
        }

        parent::__construct(['action' => 'credentialtype.php'], ['method' => 'POST'], $formContents);
        // $this->setTagProperty('enctype', 'multipart/form-data');

        $submitRow = new \Ease\TWB4\Row();

        if (null === $credtype->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('#', '🚀 '._('Test'), 'disabled btn-lg btn-block'));
        } else {
            $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?id='.$credtype->getMyKey().'&test=true', '🚀 '._('Test'), 'success btn-lg btn-block'));
        }

        $submitRow->addColumn(8, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

        if (null === $credtype->getMyKey()) {
            $submitRow->addColumn(2, new \Ease\TWB4\SubmitButton('⚰️ '._('Remove').' !', 'disabled btn-lg btn-block', ['disabled' => 'true']));
        } else {
            $this->addItem(new \Ease\Html\InputHiddenTag('id', $credtype->getMyKey()));

            if (WebPage::getRequestValue('remove') === 'true') {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?delete='.$credtype->getMyKey(), '⚰️ '._('Remove').' !', 'danger btn-lg btn-block'));
            } else {
                $submitRow->addColumn(2, new \Ease\TWB4\LinkButton('credentialtype.php?id='.$credtype->getMyKey().'&remove=true', '⚰️ '._('Remove').' ?', 'warning btn-lg btn-block'));
            }
        }

        $this->addItem($submitRow);
    }

    public function afterAdd(): void
    {
    }

    private function credTypeField(string $credTypeId, \MultiFlexi\ConfigFieldWithHelper $field): \Ease\TWB4\Row
    {
        $credTypeFieldRow = new \Ease\TWB4\Row();

        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag($credTypeId.'[keyname]', $field->getCode())));

        if ($field->isManual()) {
            $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Type'), new CfgFieldTypeSelect($credTypeId.'[type]', $field->getType())));
            $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Hint'), new \Ease\Html\InputTextTag($credTypeId.'[hint]', $field->getHint())));
            $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Default Value'), new \Ease\Html\InputTextTag($credTypeId.'[defval]', $field->getDefaultValue())));
        }

        $helper = $this->credType->getHelper();

        if ($helper) {
            $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Helper Class field'), $helper->providedFieldsSelect($credTypeId.'[helper]', $field->getHelper())));
        } else {
            $credTypeFieldRow->addColumn(2, _('No Helper Class chosen yet'));
        }

        if (is_numeric($credTypeId)) {
            $credTypeFieldRow->addColumn(1, new \Ease\TWB4\FormGroup(_('Remove'), new \Ease\Html\ATag('?removefield='.$credTypeId.'&id='.$this->credType->getMyKey(), '❌️')));
        }

        return $credTypeFieldRow;
    }
}
