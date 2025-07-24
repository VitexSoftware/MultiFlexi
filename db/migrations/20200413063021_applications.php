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
class Applications extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('apps');
        $table
            ->addColumn('enabled', 'boolean', ['default' => false])
            ->addColumn('image', 'text', ['null' => true])
            ->addColumn('nazev', 'string', ['null' => true, 'limit' => 32])
            ->addColumn('popis', 'string', ['comment' => 'App Description'])
            ->addColumn('executable', 'string', ['comment' => '/usr/bin/runme'])
            ->addColumn('DatCreate', 'datetime', [])
            ->addColumn('DatUpdate', 'datetime', ['null' => true])
            ->addIndex(['nazev'], ['unique' => true])
            ->addIndex(['executable'], ['unique' => true])
            ->create();

        //                if ($this->adapter->getAdapterType() != 'sqlite') {
        //                    $table
        //                        ->changeColumn('id', 'integer', ['identity' => true])
        //                        ->save();
        //                }
    }
}
