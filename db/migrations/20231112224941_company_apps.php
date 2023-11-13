<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyApps extends AbstractMigration
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
        
        if ($this->adapter->getAdapterType() == 'sqlite') {
            $int = 'integer';
        } else {
            $int = 'biginteger'; 
        }
     
        $int = 'integer'; // 
        $signed = true;   // 
        
        $table = $this->table('companyapp');
        $table->addColumn('app_id', $int, ['null' => false,'signed' => $signed])
        ->addColumn('company_id', $int, ['null' => false,'signed' => $signed])
        ->addIndex(['app_id', 'company_id'], ['unique' => true])
        ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'a2c-app_must_exist'])
        ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'a2c-company_must_exist']);
        $table->save();

    }
}
