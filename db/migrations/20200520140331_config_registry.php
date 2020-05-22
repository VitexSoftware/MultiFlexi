<?php

use Phinx\Migration\AbstractMigration;

class ConfigRegistry extends AbstractMigration
{
    public function change()
    {
        $customFields = $this->table('conffield');
        $customFields->addColumn('app_id', 'integer', array('null' => false))
        ->addColumn('keyname','string',['length'=>64])
        ->addColumn('type','string',['length'=>32])
        ->addColumn('description','string',['length'=>1024])
        ->addIndex(['app_id', 'keyname'], ['unique' => true])
        ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'app_must_exist']);
        $customFields->create();
                
        $configs = $this->table('configuration');
        $configs->addColumn('app_id', 'integer', array('null' => false))
        ->addColumn('company_id', 'integer', array('null' => false))
        ->addColumn('key','string',['length'=>64])
        ->addColumn('value','string',['length'=>1024])
        ->addIndex(['app_id', 'company_id','key'], ['unique' => true])
        ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'app_must_exist'])
        ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'company_must_exist']);
        $configs->create();
        
    }
}
