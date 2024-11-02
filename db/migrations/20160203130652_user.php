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

class User extends AbstractMigration
{
    public function change(): void
    {
        // Migration for table users
        $table = $this->table('user');
        $table
            ->addColumn('enabled', 'boolean', ['default' => false])
            ->addColumn('settings', 'text', ['null' => true])
            ->addColumn('email', 'string', ['limit' => 128])
            ->addColumn('firstname', 'string', ['null' => true, 'limit' => 32])
            ->addColumn('lastname', 'string', ['null' => true, 'limit' => 32])
            ->addColumn('password', 'string', ['limit' => 40])
            ->addColumn('login', 'string', ['limit' => 32])
            ->addColumn('DatCreate', 'datetime', [])
            ->addColumn('DatSave', 'datetime', ['null' => true])
            ->addColumn('last_modifier_id', 'integer', ['null' => true])
            ->addIndex(['login', 'email'], ['unique' => true])
            ->create();

        if ($this->adapter->getAdapterType() !== 'sqlite') {
            $table
                ->changeColumn('id', 'integer', ['identity' => true])
                ->save();
        }
    }
}
