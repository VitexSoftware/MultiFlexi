<?php

namespace AbraFlexi\MultiFlexi;

/**
 * Multi Flexi - Customer Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2022 Vitex Software
 */
class Customer extends DBEngine {

    public $nameColumn = 'login';
    public $myTable = 'customer';
    public $keyword = 'customer';
    public $createColumn = 'DatCreate';
    public $modifiedColumn = 'DatSave';

    /**
     * Customer's page link
     * 
     * @return string
     */
    public function getLink() {
        return 'customer.php?id=' . $this->getMyKey();
    }

    public function getUserName() {
        return $this->getRecordName();
    }

    /**
     * 
     * @param array $columns
     * 
     * @return array
     */
    public function columns($columns = []) {

//+-----------+--------------+------+-----+---------+----------------+
//| Field     | Type         | Null | Key | Default | Extra          |
//+-----------+--------------+------+-----+---------+----------------+
//| id        | int(11)      | NO   | PRI | NULL    | auto_increment |
//| enabled   | tinyint(1)   | NO   |     | 0       |                |
//| settings  | text         | YES  |     | NULL    |                |
//| email     | varchar(128) | NO   |     | NULL    |                |
//| firstname | varchar(32)  | YES  |     | NULL    |                |
//| lastname  | varchar(32)  | YES  |     | NULL    |                |
//| password  | varchar(40)  | NO   |     | NULL    |                |
//| login     | varchar(32)  | NO   | MUL | NULL    |                |
//| DatCreate | datetime     | NO   |     | NULL    |                |
//| DatSave   | datetime     | YES  |     | NULL    |                |
//+-----------+--------------+------+-----+---------+----------------+


        return parent::columns([
                    ['name' => 'id', 'type' => 'text', 'label' => _('ID')],
                    ['name' => 'login', 'type' => 'text', 'label' => _('Subject'),
                        'idColumn' => 'id',
                        'valueColumn' => 'login',
                        'listingPage' => 'customers.php',
                        'detailPage' => 'customer.php',
                    ],
                    ['name' => 'email', 'type' => 'text', 'label' => _('Status')],
        ]);
    }

    /**
     * Serialize only data storage
     * 
     * @return array
     */
    public function __sleep()
    {
        return ['data'];
    }

}
