<?php
namespace FlexiPeeHP\MultiSetup;
/**
 * Multi FlexiBee Setup - Customer Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */
class Customer extends \Ease\SQL\Engine
{
    public $nameColumn = 'login';
    public $myTable = 'customer';
    public $keyword = 'customer';
}
