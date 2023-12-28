<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TokenId extends AbstractMigration {

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
    public function change(): void {
        if ($this->adapter->getAdapterType() != 'sqlite') {
            $table = $this->table('user');
            $table
                    ->changeColumn('id', 'integer', ['identity' => true, 'signed' => false])
                    ->save();

            $table2 = $this->table('token');
            $table2
                    ->changeColumn('id', 'integer', ['identity' => true, 'signed' => false])
                    ->save();
        }

        $table->addForeignKey('user_id', 'user', 'id', ['constraint' => 'tokeuser_must_exist'])->save();
    }
}
