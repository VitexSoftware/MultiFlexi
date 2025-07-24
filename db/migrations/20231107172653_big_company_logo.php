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

use Phinx\Migration\AbstractMigration;

/**
 * @no-named-arguments
 */
final class BigCompanyLogo extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('company');

        if ($this->adapter->getAdapterType() === 'mysql') {
            $table
                ->changeColumn('logo', 'text', ['null' => false, 'limit' => Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
                ->update();
        }
    }
}
