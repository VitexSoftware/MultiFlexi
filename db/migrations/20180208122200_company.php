<?php

use Phinx\Migration\AbstractMigration;

class Company extends AbstractMigration {

    /**
     */
    public function change() {
        $table = $this->table('company');
        $table
                ->addColumn('enabled', 'boolean', array('default' => false))
                ->addColumn('settings', 'text', ['null' => true])
                ->addColumn('logo', 'text', ['null' => true])
                ->addColumn('abraflexi', 'integer', ['limit' => 128])
                ->addColumn('nazev', 'string', ['null' => true, 'limit' => 32])
                ->addColumn('ic', 'string', ['null' => true, 'limit' => 32])
                ->addColumn('company', 'string', ['comment' => 'Company Code'])
                ->addColumn('rw', 'boolean', ['comment' => 'Write permissions'])
                ->addColumn('setup', 'boolean', ['comment' => 'SetUP done'])
                ->addColumn('webhook', 'boolean', ['comment' => 'Webhook ready'])
                ->addColumn('DatCreate', 'datetime', [])
                ->addColumn('DatUpdate', 'datetime', ['null' => true])
                ->addIndex(['abraflexi', 'company'], ['unique' => true])
                ->create();

        if ($this->adapter->getAdapterType() != 'sqlite') {
            $table
                    ->changeColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
                    ->save();
        }
    }
}
