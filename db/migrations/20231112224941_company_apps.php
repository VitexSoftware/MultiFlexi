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
final class CompanyApps extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        // Check if the database is MySQL
        $databaseType = $this->getAdapter()->getOption('adapter');
        $unsigned = ($databaseType === 'mysql') ? ['signed' => false] : [];

        // Create the company_apps table
        $table = $this->table('company_apps');
        $table->addColumn('company_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'company_apps_company_must_exist'])
            ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'company_apps_app_must_exist']);
        $table->create();
    }
}
