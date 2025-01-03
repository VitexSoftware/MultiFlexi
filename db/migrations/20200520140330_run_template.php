<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class RunTemplate extends AbstractMigration
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

        // Check if the table already exists
        if (!$this->hasTable('runtemplate')) {
            // Create the runtemplate table
            $table = $this->table('runtemplate', ['id' => false, 'primary_key' => ['id']]);
            $table->addColumn('id', 'integer', array_merge(['null' => false, 'identity' => true], $unsigned))
                ->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
                ->addColumn('company_id', 'integer', array_merge(['null' => false], $unsigned))
                ->addColumn('interv', 'string', ['limit' => 1, 'null' => false])
                ->addColumn('prepared', 'boolean', ['default' => null, 'null' => true])
                ->addColumn('success', 'string', ['limit' => 250, 'default' => null, 'null' => true])
                ->addColumn('fail', 'string', ['limit' => 250, 'default' => null, 'null' => true])
                ->addColumn('name', 'string', ['limit' => 250, 'default' => null, 'null' => true])
                ->addColumn('delay', 'integer', ['default' => 0, 'null' => false, 'comment' => 'Time before job is started after periodic scheduler run'])
                ->addColumn('executor', 'string', ['limit' => 255, 'default' => 'Native', 'null' => false, 'comment' => 'Preferred Executor'])
                ->addIndex(['company_id'], ['name' => 'a2p-company_must_exist'])
                ->addIndex(['app_id', 'company_id'], ['name' => 'app_id', 'type' => 'btree'])
                ->addForeignKey('app_id', 'apps', 'id', ['constraint' => 'a2p-app_must_exist'])
                ->addForeignKey('company_id', 'company', 'id', ['constraint' => 'a2p-company_must_exist'])
                ->create();
        }
    }
}
