<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class LogLongblob extends AbstractMigration
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
        $table = $this->table('job');
        if ($this->adapter->getAdapterType() == 'mysql') {
            $table
                    ->changeColumn('stderr', 'blob', ['null' => false, 'default'=>'', 'limit' => Phinx\Db\Adapter\MysqlAdapter::BLOB_LONG])
                    ->changeColumn('stdout', 'blob', ['null' => false, 'default'=>'', 'limit' => Phinx\Db\Adapter\MysqlAdapter::BLOB_LONG])
                    ->update();
        }

    }
}
