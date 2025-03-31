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
            }

            if (\array_key_exists('company_id', $data) && (int) $data['company_id']) {
                $nameparts['company'] = (new Company((int) $data['company_id']))->getRecordName();
            }

            $data['name'] = implode(' / ', $nameparts);
        }

        return parent::takeData($data);
    }

    public function getHelper(): ?\MultiFlexi\credentialTypeInterface
    {
        if (!\is_object($this->helper) && $this->getDataValue('class')) {
            $credTypeClass = '\\MultiFlexi\\CredentialType\\'.$this->getDataValue('class');
            $this->helper = new $credTypeClass();

            if ($this->getMyKey()) {
                $this->helper->load($this->getMyKey());
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
        $data = parent::completeDataRow($dataRowRaw);

        if ($data['logo']) {
            $data['logo'] = (string) new \Ease\Html\ImgTag($data['logo'], $data['name'], ['style' => 'height: 50px;']);
        }

        $data['company_id'] = (string) new Ui\CompanyLinkButton(new Company($dataRowRaw['company_id']), ['style' => 'height: 50px;']);

        return $data;
    }

    public function getFields(): ConfigFields
    {
        $fields = new ConfigFields();
        $fielder = new \MultiFlexi\CrTypeField();

        foreach ($fielder->listingQuery()->where(['credential_type_id' => $this->getMyKey()]) as $fieldData) {
            $field = new ConfigFieldWithHelper((string) $fieldData['keyname'], $fieldData['type'], $fieldData['keyname'], (string) $fieldData['description']);
            $field->setHint($fieldData['hint'])->setDefaultValue($fieldData['defval'])->setRequired($fieldData['required'] === 1)->setHelper((string) $fieldData['helper']);
            $field->setMyKey($fieldData['id']);

            if (empty($fieldData['helper']) === false) {
                $fieldHelper = $this->getHelper()->fieldsProvided()->getFieldByCode($fieldData['helper']);
                $field->setManual($fieldHelper->isManual());
                $field->setRequired($fieldHelper->isRequired());
                $field->setSecret($fieldHelper->isSecret());
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
        if ($this->getDataValue('class')) {
            $helperData = $this->getHelper()->query();
            $crtypeFields = $this->getFields();

            foreach ($crtypeFields as $fieldKey => $field) {
                $helper = $field->getHelper();
                $source = $helperData->getFieldByCode($helper);

                if ($source) {
                    $field->setValue($source->getValue())->setSource($source->getSource())->setNote($source->getNote());
                }
            }
        }

        return $crtypeFields;
    }
}
