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

use Ease\SQL\Engine;

class CrTypeOption extends Engine
{
    /**
     * Table name.
     */
    public string $myTable = 'credtypedata';

    /**
     * CrTypeField constructor.
     *
     * @param mixed $init
     */
    public function __construct($init = null)
    {
        parent::__construct($init);
    }

    /**
     * Define the structure of the table.
     */
    public function getColumns(): array
    {
        return [
            'credential_type_id' => ['type' => 'integer', 'unsigned' => true, 'null' => false],
            'name' => ['type' => 'string', 'limit' => 64, 'default' => null, 'null' => true],
            'type' => ['type' => 'string', 'limit' => 32, 'default' => null, 'null' => true],
            'value' => ['type' => 'string', 'limit' => 256, 'default' => null, 'null' => true],
        ];
    }
}
