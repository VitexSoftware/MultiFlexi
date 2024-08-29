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

namespace MultiFlexi\AbraFlexi;

/**
 * Description of Server.
 *
 * @author vitex
 */
class Server extends \MultiFlexi\Engine implements \MultiFlexi\platformServer
{
    /**
     * SQL Table we use.
     */
    public string $myTable = 'servers';

    /**
     * Connection Environment by Server.
     *
     * @return array
     */
    public function getEnvironment()
    {
        $connectionData = $this->getData();

        return [
            'ABRAFLEXI_URL' => $connectionData['url'],
            'ABRAFLEXI_LOGIN' => $connectionData['user'],
            'ABRAFLEXI_PASSWORD' => $connectionData['password'],
        ];
    }
}
