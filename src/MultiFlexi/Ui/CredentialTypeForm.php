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

use Ease\TWB4\SubmitButton;

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
        $credTypeRow1->addColumn(2, new CredentialTypeLogo($credtype, ['style' => 'height: 200px']));
        $credTypeRow1->addColumn(6, [
            new \Ease\TWB4\FormGroup(_('Credential Name'), new \Ease\Html\InputTextTag('name', $credtype->getRecordName())),
            new \Ease\TWB4\FormGroup(_('Credential UUID'), new \Ease\Html\InputTextTag('uuid', $credtype->getDataValue('uuid'), ['disabled'])),
        ]);
        $helperCol = $credTypeRow1->addColumn(4, new \Ease\TWB4\FormGroup(_('Credential type Helper Class'), new CredentialTypeClassSelect('class', [], (string) $credtype->getDataValue('class'))));

        if ($credtype->getDataValue('class')) {
            $helperCol->addItem(new FieldsForm($credtype->getHelper()->fieldsInternal(), $credtype->getDataValue('class')));

            $provided = new \Ease\TWB4\Panel(new \Ease\Html\H4Tag(_('Fields provided')));

            $assigned = $credtype->getFields();

            foreach ($credtype->getHelper()->fieldsProvided() as $fieldProvided) {
                $fieldRow = new \Ease\TWB4\Row();

                $fieldRow->addColumn(4, $fieldProvided->getName().'<br>'.$fieldProvided->getType());
                $fieldRow->addColumn(6, $fieldProvided->getDescription());

                $flags = new \Ease\Html\SpanTag();

                if (\is_object($assigned->getFieldByCode($fieldProvided->getCode()))) {
                    $flags->addItem(new \Ease\TWB4\LinkButton('?id='.$credtype->getMyKey().'&addField='.$fieldProvided->getCode(), '➕', 'disabled', ['title' => _('Already assigned')]));
                } else {
                    $flags->addItem(new \Ease\TWB4\LinkButton('?id='.$credtype->getMyKey().'&addField='.$fieldProvided->getCode(), '➕', 'success', ['title' => _('Add Field to ')]));
                }

                $fieldRow->addColumn(2, $flags);

                $provided->addItem($fieldRow);
            }

            $helperCol->addItem($provided);
        }

        $formContents[] = $credTypeRow1;

        $formContents[] = new \Ease\Html\DivTag($this->credTypeField('new'), ['class' => 'border border-primary rounded']);

        $fields = $credtype->getFields();

        foreach ($fields as $crTypeField) {
            $fieldData = $crTypeField->getData();
            $formContents[] = $this->credTypeField((string) $crTypeField->getMykey(), $fieldData);
        }

        parent::__construct(['action' => 'credentialtype.php'], ['method' => 'POST'], $formContents);
        // $this->setTagProperty('enctype', 'multipart/form-data');

        $this->addItem(new SubmitButton(_('Save'), 'success btn-lg btn-block'));

        $submitRow = new \Ease\TWB4\Row();
        $submitRow->addColumn(10, new \Ease\TWB4\SubmitButton('🍏 '._('Apply'), 'primary btn-lg btn-block'));

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
    }

    public function afterAdd(): void
    {
    }

    private function credTypeField(string $credTypeId = 'new', array $fieldData = ['keyname' => '', 'type' => '', 'hint' => '', 'defval' => '', 'helper' => '']): \Ease\TWB4\Row
    {
        $credTypeFieldRow = new \Ease\TWB4\Row();
        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Name'), new \Ease\Html\InputTextTag($credTypeId.'[keyname]', $fieldData['keyname'])));
        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Type'), new CfgFieldTypeSelect($credTypeId.'[type]', $fieldData['type'])));
        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Hint'), new \Ease\Html\InputTextTag($credTypeId.'[hint]', $fieldData['hint'])));
        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Field Default Value'), new \Ease\Html\InputTextTag($credTypeId.'[defval]', $fieldData['defval'])));

        $credTypeFieldRow->addColumn(2, new \Ease\TWB4\FormGroup(_('Helper Class field'), $this->credType->getHelper()->providedFieldsSelect($credTypeId.'[helper]', $fieldData['helper'])));

        if (is_numeric($credTypeId)) {
            $credTypeFieldRow->addColumn(1, new \Ease\TWB4\FormGroup(_('Remove'), new \Ease\Html\ATag('?removefield='.$credTypeId.'&id='.$this->credType->getMyKey(), '❌️')));
        }

        return $credTypeFieldRow;
    }
}
