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

namespace MultiFlexi\Pohoda;

/**
 * Description of Server.
 *
 * @author vitex
 */
class Server extends \MultiFlexi\Engine implements \MultiFlexi\platformServer
{
    public function __construct($identifier = null, $options = []) {
        $this->myTable = 'servers';
        parent::__construct($identifier, $options);
    }
    
    /**
     * Pohoda Server Environment variables.
     *
     * @return array
     */
    public function getEnvironment()
    {
        return [
            'POHODA_URL' => ['value' => $this->getDataValue('url')],
            'POHODA_USERNAME' => ['value' => $this->getDataValue('user')],
            'POHODA_PASSWORD' => ['value' => $this->getDataValue('password')],
        ];
    }
}
