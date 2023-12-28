<?php

use Phinx\Migration\AbstractMigration;

class CompanyNotifyEmail extends AbstractMigration {

    public function change() {
        $refTable = $this->table('company');
        $refTable->addColumn('email', 'string', ['null' => true, 'length' => 64])->save();
    }
}
