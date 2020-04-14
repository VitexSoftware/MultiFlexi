<?php

use Phinx\Migration\AbstractMigration;

class AppToCompany extends AbstractMigration {

    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    addCustomColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Any other destructive changes will result in an error when trying to
     * rollback the migration.
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change() {
        $table = $this->table('appcompany');
        $table->addColumn('app_id', 'integer', array('null' => false))
        ->addColumn('company_id', 'integer', array('null' => false))
        ->addIndex(['app_id', 'company_id'], ['unique' => true])
        ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'app_must_exist'])
        ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'company_must_exist']);

        $table->save();
    }

}
