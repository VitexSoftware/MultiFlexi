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

class Company extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('company');
        $table
            ->addColumn('enabled', 'boolean', ['default' => false])
            ->addColumn('settings', 'text', ['null' => true])
            ->addColumn('logo', 'text', ['null' => true])
            ->addColumn('abraflexi', 'integer', ['limit' => 128])
            ->addColumn('nazev', 'string', ['null' => true, 'limit' => 32])
            ->addColumn('ic', 'string', ['null' => true, 'limit' => 32])
            ->addColumn('company', 'string', ['comment' => 'Company Code'])
            ->addColumn('rw', 'boolean', ['comment' => 'Write permissions'])
            ->addColumn('setup', 'boolean', ['comment' => 'SetUP done'])
            ->addColumn('webhook', 'boolean', ['comment' => 'Webhook ready'])
            ->addColumn('DatCreate', 'datetime', [])
            ->addColumn('DatUpdate', 'datetime', ['null' => true])
            ->addIndex(['abraflexi', 'company'], ['unique' => true])
            ->create();

        //        if ($this->adapter->getAdapterType() != 'sqlite') {
        //            $table
        //                    ->changeColumn('id', 'integer', ['identity' => true])
        //                    ->save();
        //        }
    }
}
