<?php

use Phinx\Migration\AbstractMigration;

class ConfigRegistry extends AbstractMigration {

    public function change() {
        $customFields = $this->table('conffield');
        $customFields->addColumn('app_id', 'integer', ['null' => false,'signed'=>false])
                ->addColumn('keyname', 'string', ['length' => 64])
                ->addColumn('type', 'string', ['length' => 32])
                ->addColumn('description', 'string', ['length' => 1024])
                ->addIndex(['app_id', 'keyname'], ['unique' => true])
                ->addForeignKey('app_id', 'apps', 'id', ['constraint' => 'cff-app_must_exist', 'delete'=>'CASCADE'])
        ;
        $customFields->create();

        $configs = $this->table('configuration');
        $configs->addColumn('app_id', 'integer', ['null' => false,'signed'=>false])
                ->addColumn('company_id', 'integer', ['null' => false,'signed'=>false])
                ->addColumn('key', 'string', ['length' => 64])
                ->addColumn('value', 'string', ['length' => 1024])
                ->addColumn('runtemplate_id', 'integer', ['null' => false,'signed'=>false])
                ->addIndex(['app_id', 'company_id'])
                ->addIndex(['runtemplate_id', 'key'], ['unique' => true])
                ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'cfg-app_must_exist', 'delete'=>'CASCADE'])
                ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'cfg-company_must_exist', 'delete'=>'CASCADE'])
        ;
        $configs->create();
    }

}
