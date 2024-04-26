<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyEnv extends AbstractMigration {

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
    public function change() {
        $table = $this->table('companyenv');
        $table->addColumn('keyword', 'string', array('null' => false))
                ->addColumn('value', 'string', array('null' => false))
                ->addColumn('company_id', 'integer', ['null' => false, 'unsigned'=>false])
                ->addIndex(['keyword', 'company_id'], ['unique' => true])
                ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'env-company_must_exist']);
        $table->save();
    }
}
