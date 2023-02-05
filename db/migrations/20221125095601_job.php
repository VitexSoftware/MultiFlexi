<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Job extends AbstractMigration {

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
        $table = $this->table('job');
        $table->addColumn('app_id', 'biginteger', ['null' => false, 'signed' => false])
                ->addColumn('begin', 'datetime', ['default' => 'CURRENT_TIMESTAMP'])
                ->addColumn('end', 'datetime', ['null' => true])
                ->addColumn('company_id', 'biginteger', ['null' => false,'signed'=>false])
                ->addColumn('exitcode', 'integer', ['null' => true])
                ->addForeignKey('app_id', 'apps', ['id'],
                        ['constraint' => 'job-app_must_exist'])
                ->addForeignKey('company_id', 'company', ['id'],
                        ['constraint' => 'job-company_must_exist']);
        $table->save();
    }

}
