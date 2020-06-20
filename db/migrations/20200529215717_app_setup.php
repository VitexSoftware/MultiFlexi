<?php

use Phinx\Migration\AbstractMigration;

class AppSetup extends AbstractMigration
{
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
    public function change()
    {
        $table = $this->table('apps');
        $table
                ->addColumn('setup', 'string', ['null' => true, 'limit' => 256])
                ->save();
    }
}
