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

final class Artifacts extends AbstractMigration
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
        $table = $this->table('artifacts');

        if ($this->adapter->getAdapterType() !== 'sqlite') {
            $table->addColumn('job_id', 'integer', ['signed' => false])
                ->addColumn('filename', 'string', ['null' => true])
                ->addColumn('content_type', 'string', ['default' => 'text/plain'])
                ->addColumn('artifact', 'text', ['limit' => \Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('job_id', 'job', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        } else {
            $table->addColumn('job_id', 'integer', ['signed' => false])
                ->addColumn('filename', 'string', ['null' => true])
                ->addColumn('content_type', 'string', ['default' => 'text/plain'])
                ->addColumn('artifact', 'text')
                ->addColumn('created_at', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addForeignKey('job_id', 'job', 'id', ['delete' => 'CASCADE', 'update' => 'NO_ACTION'])
                ->create();
        }
    }
}
