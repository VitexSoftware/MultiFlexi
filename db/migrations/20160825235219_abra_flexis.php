<?php

use Phinx\Migration\AbstractMigration;

class AbraFlexis extends AbstractMigration
{
    /**
     */
    public function change()
    {
        $table = $this->table('abraflexis');
        $table->addColumn('name', 'string',
            ['comment' => 'AbraFlexi instance Name'])
            ->addColumn('url', 'string', ['comment' => 'RestAPI endpoint url'])
            ->addColumn('user', 'string', ['comment' => 'REST API Username'])
            ->addColumn('password', 'string', ['comment' => 'Rest API Password'])
            ->addColumn('DatCreate', 'datetime')
            ->addColumn('DatSave', 'datetime', ['null' => true])
            ->addIndex(['url'],['unique' => true, 'name' => 'fbs_uniq'])
            ->create();
    }
}
