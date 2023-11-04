<?php
declare(strict_types=1);
/**
 * Multi Flexi - Pohoda Server
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Pohoda;

/**
 * Description of Server
 *
 * @author vitex
 */
class Server extends \MultiFlexi\Engine implements \MultiFlexi\platformServer
{
    /**
     * SQL Table we use
     * @var string
     */
    public $myTable = 'servers';

    /**
     * Pohoda Server Environment variables 
     * 
     * @return array 
     */
    public function getEnvironment()
    {
        return [
            'POHODA_URL' => $this->getDataValue('url'),
            'POHODA_USERNAME' => $this->getDataValue('user'),
            'POHODA_PASSWORD' => $this->getDataValue('password')
        ];
    }
}
