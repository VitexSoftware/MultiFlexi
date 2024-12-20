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

class AppToCompany extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('appcompany');
        $table->addColumn('app_id', 'integer', ['null' => false])
            ->addColumn('company_id', 'integer', ['null' => false])
            ->addColumn('interval', 'string', ['length' => 1])
            ->addIndex(['app_id', 'company_id'])
            ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'a2p_app_must_exist'])
            ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'a2p_company_must_exist']);
        $table->save();
    }
}
