<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ActionConfig extends AbstractMigration
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
    public function change(): void
    {
        $table = $this->table('actionconfig', ['comment' => 'Module per company configurations']);
        $table
                ->addColumn('module', 'string', ['comment' => 'Configuration belongs to'])
                ->addColumn('keyname', 'string', ['comment' => 'Configuration Key name'])
                ->addColumn('value', 'string', ['comment' => 'Configuration Value'])
                ->addColumn('mode', 'string', ['null' => true, 'length' => 10, 'default' => null, 'comment' => 'success, fail or empty'])
                ->addColumn('runtemplate_id', 'integer', ['null' => false,'signed'=>false])
                ->addIndex(['module', 'keyname', 'mode', 'runtemplate_id'], ['unique' => true])
                ->addForeignKey('runtemplate_id', 'runtemplate', ['id'], ['constraint' => 'runtemplate_must_exist'])
                ->create();
    }
}
