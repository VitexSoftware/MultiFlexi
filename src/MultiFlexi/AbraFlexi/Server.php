<?php

declare(strict_types=1);

/**
 * Multi Flexi - AbraFlexi Server
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\AbraFlexi;

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
     * Connection Environment by Server
     *
     * @return array
     */
    public function getEnvironment()
    {
        $connectionData = $this->getData();
        $companyEnvironment = [
            'ABRAFLEXI_URL' => $connectionData['url'],
            'ABRAFLEXI_LOGIN' => $connectionData['user'],
            'ABRAFLEXI_PASSWORD' => $connectionData['password'],
        ];
        return $companyEnvironment;
    }
}
