<?php

use Phinx\Migration\AbstractMigration;

class Applications extends AbstractMigration {

    /**
     */
    public function change() {
        $table = $this->table('apps');
        $table
                ->addColumn('enabled', 'boolean', ['default' => false])
                ->addColumn('image', 'text', ['null' => true])
                ->addColumn('nazev', 'string', ['null' => true, 'limit' => 32])
                ->addColumn('popis', 'string', ['comment' => 'App Description'])
                ->addColumn('executable', 'string', ['comment' => '/usr/bin/runme'])
                ->addColumn('DatCreate', 'datetime', [])
                ->addColumn('DatUpdate', 'datetime', ['null' => true])
                ->addIndex(['nazev'], ['unique' => true])
                ->addIndex(['executable'], ['unique' => true])
                ->create();
        
//                if ($this->adapter->getAdapterType() != 'sqlite') {
//                    $table
//                        ->changeColumn('id', 'integer', ['identity' => true, 'signed' => false])
//                        ->save();
//                }
            }

}
