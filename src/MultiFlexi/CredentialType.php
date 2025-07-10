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
 * Description of CredentialType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialType extends DBEngine
{
    /**
     * @var string Name Column
     */
    public string $nameColumn = 'name';
    private ?\MultiFlexi\credentialTypeInterface $helper = null;

    public function __construct($init = null, array $filter = [])
    {
        $this->myTable = 'credential_type';
        $this->setDataValue('uuid', \Ease\Functions::guidv4());
        parent::__construct($init, $filter);
    }

    /**
     * Prepare data for save.
     */
    #[\Override]
    public function takeData(array $data): int
    {
        if (\array_key_exists('id', $data) && is_numeric($data['id'])) {
            unset($data['uuid']);
        } else {
            if (\array_key_exists('uuid', $data) === false) {
                $data['uuid'] = \Ease\Functions::guidv4();
            }
        }

        if (\array_key_exists('id', $data) && !is_numeric($data['id'])) {
            unset($data['id']);
        }

        if (\array_key_exists('name', $data) && empty($data['name'])) {
            $nameparts = [];

            if (\array_key_exists('class', $data) && $data['class']) {
                $credTypeClass = '\\MultiFlexi\\CredentialType\\'.$data['class'];
                $nameparts['class'] = $credTypeClass::name();
                $this->getHelper();
            }

            if (\array_key_exists('company_id', $data) && (int) $data['company_id']) {
                $nameparts['company'] = (new Company((int) $data['company_id']))->getRecordName();
            }

            $data['name'] = implode(' / ', $nameparts);
        }

        return parent::takeData($data);
    }

    public function loadFromSQL($id = null)
    {
        $loaded = parent::loadFromSQL($id);
        $class = $this->getDataValue('class');

        if ($class) {
            $this->getHelper();

            if (empty($this->getDataValue('logo'))) {
                $this->setDataValue('logo', $this->helper->logo());
            }
        }

        return $loaded;
    }

    public function getHelper(): ?\MultiFlexi\credentialTypeInterface
    {
        $class = $this->getDataValue('class');

        if ($class) {
            $credTypeClass = '\\MultiFlexi\\CredentialType\\'.$class;

            if ((\is_object($this->helper) === false) || (\Ease\Functions::baseClassName($this->helper) !== $class)) {
                $this->helper = new $credTypeClass();

                if ($this->getMyKey() && method_exists($this->helper, 'load')) {
                    $this->helper->load($this->getMyKey());
                }
            }
        }

        return $this->helper;
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'detailPage' => 'credentialtype.php', 'valueColumn' => 'credential_type.id', 'idColumn' => 'credential_type.id', ],
            ['name' => 'logo', 'type' => 'text', 'label' => _('Logo'), 'searchable' => false],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'uuid', 'type' => 'text', 'label' => _('UUID')],
            ['name' => 'company_id', 'type' => 'text', 'label' => _('Company')],
        ]);
    }

    #[\Override]
    public function completeDataRow(array $dataRowRaw): array
    {
        $this->setData($dataRowRaw);
        $data = parent::completeDataRow($dataRowRaw);

        $helperClass = \get_class($this->getHelper() ?? new CredentialType\Common());

        if (empty($data['logo'])) {
            $data['logo'] = 'images/'.$helperClass::logo();
        }

        if (empty($data['name'])) {
            $data['name'] = $helperClass::name();
        }

        $data['logo'] = (string) new \Ease\Html\ATag('credentialtype.php?id='.$this->getMyKey(), new \Ease\Html\ImgTag($data['logo'], $data['name'], ['title' => $data['name'], 'style' => 'height: 50px;']));

        $data['company_id'] = (string) new Ui\CompanyLinkButton(new Company($dataRowRaw['company_id']), ['style' => 'height: 50px;']);

        return $data;
    }

    public function getFields(): ConfigFields
    {
        $fields = new ConfigFields();
        $fielder = new \MultiFlexi\CrTypeField();

        foreach ($this->getHelper()->fieldsProvided() as $providedField) {
            if ($providedField->isRequired()) {
                $rField = new ConfigFieldWithHelper($providedField->getCode(), $providedField->getType(), $providedField->getName(), $providedField->getDescription());
                $rField->setHint($providedField->getHint())->setDefaultValue($providedField->getDefaultValue())->setRequired(true)->setManual($providedField->isManual())->setMultiLine($providedField->isMultiline())->setHelper(\Ease\Functions::baseClassName($this->getHelper()));
                $fields->addField($rField);
            }
        }

        foreach ($fielder->listingQuery()->where(['credential_type_id' => $this->getMyKey()]) as $fieldData) {
            $field = new ConfigFieldWithHelper((string) $fieldData['keyname'], $fieldData['type'], $fieldData['keyname'], (string) $fieldData['description']);
            $field->setHint($fieldData['hint'])->setDefaultValue($fieldData['defval'])->setRequired($fieldData['required'] === 1)->setHelper((string) $fieldData['helper']);
            $field->setMyKey($fieldData['id']);

            if (empty($fieldData['helper']) === false) {
                $fieldHelper = $this->getHelper()->fieldsProvided()->getFieldByCode($fieldData['helper']);

                if ($fieldHelper) {
                    $field->setManual($fieldHelper->isManual());
                    $field->setRequired($fieldHelper->isRequired());
                    $field->setSecret($fieldHelper->isSecret());
                } else {
                    $this->addStatusMessage(sprintf(_('Unexistent field helper %s ?!?'), $fieldData['helper']), 'info'); // TODO:
                }
            }

            $fields->addField($field);
        }

        return $fields;
    }

    public function getCredTypeFields(self $credentialType): ConfigFields
    {
    }

    public function query(): ConfigFields
    {
        $fields = $this->getFields();

        if ($this->getHelper()) {
            $fields->addFields($this->getHelper()->query());
        }

        return $fields;
    }

    public function getLogo(): string
    {
        return (string) $this->getDataValue('logo');
    }
}
