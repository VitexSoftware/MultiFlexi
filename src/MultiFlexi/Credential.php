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

class Credential extends DBEngine
{
    private Credata $credator;

    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'credentials';
        $this->keyColumn = 'id';
        $this->nameColumn = 'name';
        $this->credator = new Credata();
        parent::__construct($identifier, $options);
    }

    public function takeData(array $data): int
    {
        if (\array_key_exists('name', $data) === false || empty($data['name'])) {
            if ($data['company_id']) {
                $companer = new Company((int) $data['company_id']);

                $data['name'] = $companer->getRecordName().' '.$data['formType'].' '.$data['id'];
            } else {
                $data['name'] = $data['formType'].' '.$data['id'];
            }
        }

        if (empty($data['id'])) {
            unset($data['id']);
        }

        return parent::takeData($data);
    }

    public function insertToSQL($data = null): int
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $fieldData = [];

        if (\array_key_exists('formType', $data)) {
            $class = '\\MultiFlexi\\Ui\\Form\\'.$data['formType'];

            if (class_exists($class)) {
                $formColumns = $class::fields();

                foreach ($formColumns as $filed => $properties) {
                    if (\array_key_exists($filed, $data)) {
                        $fieldData[$filed] = $properties;
                        $fieldData[$filed]['value'] = $data[$filed];
                        unset($data[$filed]);
                    }
                }
            }
        }

        $recordId = parent::insertToSQL($data);

        if ($fieldData) {
            foreach ($fieldData as $filedName => $fieldProperties) {
                $this->credator->insertToSQL(
                    [
                        'credential_id' => $recordId,
                        'name' => $filedName,
                        'value' => $fieldProperties['value'],
                        'type' => $fieldProperties['type'],
                    ],
                );
            }
        }

        return $recordId;
    }

    public function updateToSQL($data = null, $conditons = []): int
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $originalData = $data;

        if (\array_key_exists('formType', $data)) {
            $class = '\\MultiFlexi\\Ui\\Form\\'.$data['formType'];

            if (class_exists($class)) {
                $currentData = $this->credator->listingQuery()->where('credential_id', $this->getMyKey())->fetchAll('name');
                $fields = $class::fields();

                foreach (\array_keys($fields) as $filed) {
                    if (\array_key_exists($filed, $data)) {
                        if (\array_key_exists($filed, $currentData)) {
                            $this->credator->updateToSQL(
                                ['value' => $data[$filed]],
                                [
                                    'credential_id' => $this->getMyKey(),
                                    'name' => $filed,
                                ],
                            );
                        } else {
                            $this->credator->insertToSQL(
                                ['value' => $data[$filed],
                                    'credential_id' => $this->getMyKey(),
                                    'name' => $filed,
                                    'type' => $fields[$filed]['type'],
                                ],
                            );
                        }

                        unset($data[$filed]);
                    }
                }
            }
        }

        $this->takeData($originalData);

        return parent::updateToSQL($data, $conditons);
    }

    public function loadFromSQL($itemID = null)
    {
        if (null === $itemID) {
            $itemID = $this->getMyKey();
        }

        $data = parent::loadFromSQL($itemID);

        foreach ($this->credator->listingQuery()->where('credential_id', $this->getMyKey()) as $credential) {
            $this->setDataValue($credential['name'], $credential['value']);
        }

        return $data;
    }

    public function deleteFromSQL($data = null)
    {
        $this->credator->deleteFromSQL(['credential_id' => $this->getMyKey()]);

        return parent::deleteFromSQL($data);
    }

    public function getCompanyCredentials(int $companyId, $appRequirements = []): array
    {
        $companyCredentials = $this->listingQuery()->where('company_id', $companyId);

        foreach ($appRequirements as $req) {
            $companyCredentials->whereOr('formType', $req);
        }

        return $companyCredentials->fetchAll('id');
    }

    /**
     * @see https://datatables.net/examples/advanced_init/column_render.html
     *
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return <<<'EOD'

"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]
,

EOD;
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'), 'detailPage' => 'credential.php', 'valueColumn' => 'credentials.id', 'idColumn' => 'credentials.id'],
            ['name' => 'formType', 'type' => 'text', 'label' => _('Type')],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'company', 'type' => 'text', 'label' => _('Company')],
        ]);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $dataRow['id'] = $dataRowRaw['id'];
        $dataRow['name'] = '<a title="'.$dataRowRaw['name'].'" href="credential.php?id='.$dataRowRaw['id'].'">'.$dataRowRaw['name'].'</a>';
        $dataRow['formType'] = $dataRowRaw['formType'].'<br><a title="'.$dataRowRaw['formType'].'" href="credential.php?id='.$dataRowRaw['id'].'"><img src="images/'.$dataRowRaw['formType'].'.svg" height="50">';
        $dataRow['company'] = (string) new Ui\CompanyLinkButton(new Company($dataRowRaw['company_id']), ['style' => 'height: 50px;']);

        return parent::completeDataRow($dataRow);
    }
}
