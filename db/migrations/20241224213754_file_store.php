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

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

/**
 * @no-named-arguments
 */
final class FileStore extends AbstractMigration
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
        $fileColumnOptions = ($databaseType === 'mysql') ? ['limit' => MysqlAdapter::BLOB_LONG] : [];

        // Create the file_store table
        $table = $this->table('file_store');
        $table->addColumn('job_id', 'integer', array_merge(['null' => true], $unsigned))
            ->addColumn('runtemplate_id', 'integer', array_merge(['null' => true], $unsigned))
            ->addColumn('field', 'string', ['limit' => 255])
            ->addColumn('file_name', 'string', ['limit' => 255])
            ->addColumn('file_path', 'string', ['limit' => 255])
            ->addColumn('file_data', 'binary', array_merge(['null' => true], $fileColumnOptions))
            ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
            ->addForeignKey('job_id', 'job', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->addForeignKey('runtemplate_id', 'runtemplate', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
            ->create();
    }
}
