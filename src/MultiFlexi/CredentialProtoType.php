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

namespace MultiFlexi;

/**
 * Description of CredentialProtoType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
abstract class CredentialProtoType extends \Ease\Sand
{
    use \Ease\recordkey;
    protected \MultiFlexi\ConfigFields $configFieldsProvided;
    protected \MultiFlexi\ConfigFields $configFieldsInternal;

    public function __construct()
    {
        $this->configFieldsProvided = new \MultiFlexi\ConfigFields();
        $this->configFieldsInternal = new \MultiFlexi\ConfigFields();
        $this->setObjectName();
    }

    public function load(int $credTypeId)
    {
        $loader = new \MultiFlexi\CrTypeOption();

        return $this->takeData($loader->listingQuery()->where('credential_type_id', $credTypeId)->fetchAll('name'));
    }

    public function save(): bool
    {
        $credentialTypeId = $this->getDataValue('credential_type_id');

        $fielder = new \MultiFlexi\CrTypeOption();

        foreach ($this->fieldsInternal() as $keyName => $field) {
            $fielder->dataReset();
            $subject = ['name' => $keyName, 'credential_type_id' => $credentialTypeId];
            $fielder->loadFromSQL($subject);
            $rowId = $fielder->getMyKey();
            $fielder->dataReset();

            if ($rowId) {
                $fielder->setMyKey($rowId);
            }

            $fielder->takeData(array_merge($subject, ['type' => $field->getType(), 'value' => $field->getValue()]));
            $fielder->saveToSQL();
        }

        return true;
    }

    /**
     * Set defVal where value is not set.
     */
    public function prepareConfigForm(): void
    {
        foreach ($this->configFieldsInternal as $folderField) {
            if (null === $folderField->getValue()) {
                $folderField->setValue($folderField->getDefaultValue());
                $this->addStatusMessage(sprintf(_('%s prefilled from default'), $folderField->getCode()));
            }
        }
    }

    /**
     * Choose one of provided fields.
     *
     * @param array<string, string> $properties
     */
    public function providedFieldsSelect(string $name, string $defaultValue = '', array $properties = []): \Ease\Html\SelectTag
    {
        $items = ['' => _('Choose Provided field')];

        foreach ($this->configFieldsProvided as $configField) {
            $items[$configField->getCode()] = $configField->getName();
        }

        return new \Ease\Html\SelectTag($name, $items, $defaultValue, $properties);
    }

    public function takeData($data): int
    {
        $imported = 0;

        foreach ($data as $key => $fieldData) {
            $field = $this->configFieldsInternal->getFieldByCode($key);

            if ($field) {
                $field->setValue(\is_string($fieldData) ? $fieldData : $fieldData['value']);
                ++$imported;
            } else {
                $this->setDataValue($key, $fieldData);
            }
        }

        return $imported;
    }

    public function fieldsProvided(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsProvided;
    }

    public function fieldsInternal(): \MultiFlexi\ConfigFields
    {
        return $this->configFieldsInternal;
    }

    public function checkInternalFields() {
        return true;
    }
    
    public function checkProvidedFields() {
        return true;
    }
    
    public function query(): ConfigFields
    {
        return $this->fieldsProvided();
    }
}
