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

class AbraFlexis extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('abraflexis');
        $table->addColumn(
            'name',
            'string',
            ['comment' => 'AbraFlexi instance Name'],
        )
            ->addColumn('url', 'string', ['comment' => 'RestAPI endpoint url'])
            ->addColumn('user', 'string', ['comment' => 'REST API Username'])
            ->addColumn('password', 'string', ['comment' => 'Rest API Password'])
            ->addColumn('DatCreate', 'datetime')
            ->addColumn('DatSave', 'datetime', ['null' => true])
            ->addIndex(['url'], ['unique' => true, 'name' => 'fbs_uniq'])
            ->create();
    }
}
