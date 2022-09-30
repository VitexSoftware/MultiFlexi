<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace AbraFlexi\MultiFlexi;

use DateTime;
use Ease\ui\LiveAge;
use Envms\FluentPDO\Literal;
use SensioLabs\AnsiConverter\AnsiToHtmlConverter;

/**
 * Description of Dashboarder
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class Logger extends DBEngine {

    public $myTable = 'log';
    public $myKeyColumn = 'id';
    public $createColumn = 'created';
    public $modifiedColumn = 'created';
    public $nameColumn = 'heading';

    /**
     * Search resuts targeting to  here
     * @var string 
     */
    public $keyword = 'dbmessage';

    /**
     * 
     * @param int $id
     */
    public function __construct($id = null) {
        parent::__construct($id);
    }

    /**
     * 
     * @return array
     */
    public function getActualMessages() {
        return $this->getColumnsFromSQL('*', ['resolvedby' => 0], 'created');
    }

//    /**
//     * 
//     * @return array
//     */
//    public function listingQuery() {
//        return parent::listingQuery()->select('user.*')->select('apps.*')->select('company.*')
//                        ->leftJoin('user ON user.id = log.user_id')
//                        ->leftJoin('apps ON apps.id = log.app_id')
//                        ->leftJoin('company ON company.id = log.company_id');
//    }

    /**
     * 
     * @return array
     */
    public function dismis() {
        return $this->getFluentPDO()->update($this->getMyTable())->set(['resolved' => new Literal('NOW()')])->where('id',
                        $this->getMyKey())->execute();
    }

    /**
     * 
     * @param array $columns
     * 
     * @return array
     */
    public function columns($columns = []) {

//  [company_id] => null
//  [app_id] => null
//  [user_id] => (string) 0
//  [severity] => (string) info
//  [venue] => (string) AbraFlexi\MultiFlexi\Ui\WebPage
//  [message] => (string) logged
//  [created] => (string) 2020-07-04 22:27:45
//  [enabled] => null
//  [settings] => null
//  [email] => null
//  [firstname] => (string) Demo
//  [lastname] => (string) Demo
//  [password] => (string) a26ac720512764602ce1c1ae537efb04:9d
//  [login] => (string) demo
//  [DatCreate] => null
//  [DatSave] => null
//  [last_modifier_id] => null
//  [image] => null
//  [nazev] => null
//  [popis] => null
//  [executable] => null
//  [DatUpdate] => null
//  [setup] => null
//  [logo] => null
//  [abraflexi] => null
//  [ic] => null
//  [company] => null
//  [rw] => null
//  [webhook] => null


        return parent::columns([
                    ['name' => 'id', 'type' => 'text', 'label' => _('ID')],
                    ['name' => 'severity', 'type' => 'text', 'label' => _('Status')],
                    ['name' => 'venue', 'type' => 'text', 'label' => _('Subject')],
                    ['name' => 'message', 'type' => 'text', 'label' => _('Message')],
                    ['name' => 'created', 'type' => 'datetime', 'label' => _('Created')],
//                    ['name' => 'resolved', 'type' => 'datetime', 'label' => _('Resolved')],
                    ['name' => 'application', 'type' => 'selectize', 'label' => _('Application'),
                        'listingPage' => 'apps.php',
                        'detailPage' => 'app.php',
                        'idColumn' => 'apps',
                        'valueColumn' => 'apps.nazev',
                        'engine' => '\AbraFlexi\MultiFlexi\Application',
                        'filterby' => 'name',
                    ],
                    ['name' => 'company', 'type' => 'selectize', 'label' => _('Company'),
                        'listingPage' => 'companies.php',
                        'detailPage' => 'company.php',
                        'idColumn' => 'company',
                        'valueColumn' => 'company.nazev',
                        'engine' => '\AbraFlexi\MultiFlexi\Company',
                        'filterby' => 'name',
                    ],
                    ['name' => 'user', 'type' => 'selectize', 'label' => _('User'),
                        'listingPage' => 'users.php',
                        'detailPage' => 'user.php',
                        'idColumn' => 'user',
                        'valueColumn' => 'user.login',
                        'engine' => '\AbraFlexi\MultiFlexi\User',
                        'filterby' => 'name',
                    ],
        ]);
    }

    public function tableCode($tableId) {
        return '
 "order": [[ 1, "asc" ]],
';
    }

    /**
     * @link https://datatables.net/examples/advanced_init/column_render.html 
     * 
     * @return string Column rendering
     */
    public function columnDefs() {
        return '
"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]            
,
';
    }

    public function getRecordName() {
        return trim($this->getDataValue('title') . ' ' . $this->getDataValue('name'));
    }

    public function completeDataRow(array $dataRowRaw) {
        switch ($dataRowRaw['severity']) {
            case 'success':
                $dataRowRaw['DT_RowClass'] = 'bg-success  text-white';
                break;
            case 'warning':
                $dataRowRaw['DT_RowClass'] = 'bg-warning  text-dark';
                break;
            case 'error':
                $dataRowRaw['DT_RowClass'] = 'bg-danger  text-dark';
                break;
            case 'debug':
                $dataRowRaw['DT_RowClass'] = 'bg-primary text-white';
                break;
            case 'info':
                $dataRowRaw['DT_RowClass'] = 'bg-info text-white';
                break;
            default:
                $dataRowRaw['DT_RowClass'] = 'text-dark';
                break;
        }
        
        $dataRowRaw['message'] = (new AnsiToHtmlConverter())->convert(str_replace('.........', '......... ',
                $dataRowRaw['message']));
//        $dataRowRaw['created'] = (new LiveAge((new DateTime($dataRowRaw['created']))->getTimestamp()))->__toString();

        return parent::completeDataRow($dataRowRaw);
    }

    public static function toRFC3339(string $dateTimePlain) {
        return ($dateTimePlain == '0000-00-00 00:00:00') ? null : \DateTime::createFromFormat('Y-m-d H:i:s',
                        $dateTimePlain)->format(DateTime::ATOM);
    }

}
