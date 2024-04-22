<?php

use Phinx\Migration\AbstractMigration;

class AppToCompany extends AbstractMigration {

    /**
     */
    public function change() {
        $table = $this->table('appcompany');
        $table->addColumn('app_id', 'integer', ['null' => false])
        ->addColumn('company_id', 'integer', ['null' => false])
        ->addColumn('interval','string',['length'=>1])
        ->addIndex(['app_id', 'company_id'])
        ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'a2p-app_must_exist'])
        ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'a2p-company_must_exist']);
        $table->save();
    }

}
