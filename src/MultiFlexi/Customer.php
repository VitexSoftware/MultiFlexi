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
 * MultiFlexi - Customer Management Class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2024 VitexSoftware
 */
class Customer extends DBEngine
{
    public function __construct($init = null, $filter = [])
    {
        $this->nameColumn = 'login';
        $this->myTable = 'customer';
        $this->keyword = 'customer';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatSave';
        parent::__construct($init, $filter);
    }

    /**
     * Serialize only data storage.
     */
    public function __sleep(): array
    {
        return ['data'];
    }

    public function getUserName()
    {
        return $this->getRecordName();
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        // +-----------+--------------+------+-----+---------+----------------+
        // | Field     | Type         | Null | Key | Default | Extra          |
        // +-----------+--------------+------+-----+---------+----------------+
        // | id        | int(11)      | NO   | PRI | NULL    | auto_increment |
        // | enabled   | tinyint(1)   | NO   |     | 0       |                |
        // | settings  | text         | YES  |     | NULL    |                |
        // | email     | varchar(128) | NO   |     | NULL    |                |
        // | firstname | varchar(32)  | YES  |     | NULL    |                |
        // | lastname  | varchar(32)  | YES  |     | NULL    |                |
        // | password  | varchar(40)  | NO   |     | NULL    |                |
        // | login     | varchar(32)  | NO   | MUL | NULL    |                |
        // | DatCreate | datetime     | NO   |     | NULL    |                |
        // | DatSave   | datetime     | YES  |     | NULL    |                |
        // +-----------+--------------+------+-----+---------+----------------+

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
}
