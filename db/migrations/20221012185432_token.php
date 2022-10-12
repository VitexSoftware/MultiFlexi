<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Token extends AbstractMigration {

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
    public function change() {
        $table = $this->table('token');
        $table
                ->addColumn('token', 'string', ['limit' => 64])
                ->addColumn('start', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('until', 'datetime', ['null' => true])
                ->addColumn('user_id', 'integer', ['null' => false, 'length' => 11])
                ->addForeignKey('user_id', 'user', 'id', ['constraint' => 'user_must_exist'])
                ->create();
    }

}
