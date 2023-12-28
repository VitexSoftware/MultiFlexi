<?php

use Phinx\Migration\AbstractMigration;

class CompanysOwnerIsCustomer extends AbstractMigration {

    /**
     * Every company needs owner customer
     */
    public function change() {
        $refTable = $this->table('company');
        $refTable->addColumn('customer', 'integer', ['null' => true])->addIndex(['customer'])->save();
    }

}
