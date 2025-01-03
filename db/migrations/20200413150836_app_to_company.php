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
        $databaseType = $this->getAdapter()->getOption('adapter');
        $unsigned = ($databaseType === 'mysql') ? ['signed' => false] : [];

        $table = $this->table('companyapp');
        $table->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('company_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addIndex(['app_id', 'company_id'])
            ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'a2p_app_must_exist'])
            ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'a2p_company_must_exist']);
        $table->save();
    }
}
