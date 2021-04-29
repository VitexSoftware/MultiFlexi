<?php
namespace AbraFlexi\MultiSetup;

/**
 * Multi AbraFlexi Setup - Customer Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */
class Customer extends Engine
{
    public $nameColumn = 'login';
    public $myTable = 'customer';
    public $keyword = 'customer';
    public $createColumn = 'DatCreate';
    public $updatedColumn = 'DatUpdate';
}
