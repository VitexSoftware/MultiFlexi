<?php

use Phinx\Migration\AbstractMigration;

class User extends AbstractMigration {

    /**
     */
    public function change() {
        // Migration for table users
        $table = $this->table('user');
        $table
                ->addColumn('enabled', 'boolean', array('default' => false))
                ->addColumn('settings', 'text', ['null' => true])
                ->addColumn('email', 'string', ['limit' => 128])
                ->addColumn('firstname', 'string', ['null' => true, 'limit' => 32])
                ->addColumn('lastname', 'string', ['null' => true, 'limit' => 32])
                ->addColumn('password', 'string', ['limit' => 40])
                ->addColumn('login', 'string', ['limit' => 32])
                ->addColumn('DatCreate', 'datetime', [])
                ->addColumn('DatSave', 'datetime', ['null' => true])
                ->addColumn('last_modifier_id', 'integer', ['null' => true, 'signed' => false])
                ->addIndex(['login', 'email'], ['unique' => true])
                ->create();
    }

}
