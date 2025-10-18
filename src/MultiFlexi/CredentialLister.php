<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */
namespace MultiFlexi;
/**
 * Description of CredentialLister
 *
 * @author vitex
 */
class CredentialLister extends \MultiFlexi\Credential
{
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
            ['name' => 'company_id', 'type' => 'selectize', 'label' => _('Company'),
                'listingPage' => 'companies.php',
                'detailPage' => 'company.php',
                'idColumn' => 'company_id',
                'valueColumn' => 'company.name',
                'engine' => '\MultiFlexi\Company',
                'filterby' => 'name',
            ],
        ]);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $helper = new CredentialType($dataRowRaw['credential_type_id']);
        $dataRow['id'] = $dataRowRaw['id'];
        $dataRow['name'] = '<a title="'.$dataRowRaw['name'].'" href="credential.php?id='.$dataRowRaw['id'].'">'.$dataRowRaw['name'].'</a>';
        $dataRow['formType'] = $dataRowRaw['formType'].'<br><a title="'.$dataRowRaw['formType'].'" href="credential.php?id='.$dataRowRaw['id'].'"><img src="images/'.$helper->getDataValue('logo').'" height="50">';
        $dataRow['company_id'] = (string) new Ui\CompanyLinkButton(new Company($dataRowRaw['company_id']), ['style' => 'height: 50px;']) .' '. $dataRowRaw['company_id_value'];

        return parent::completeDataRow($dataRow);
    }


}
