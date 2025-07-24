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
final class Job extends AbstractMigration
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

        // Create the job table
        $table = $this->table('job');
        $table->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('begin', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
            ->addColumn('end', 'datetime', ['null' => true])
            ->addColumn('company_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('exitcode', 'integer', array_merge(['null' => true]))
            ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'job_app_must_exist'])
            ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'job_company_must_exist']);
        $table->create();
    }
}
