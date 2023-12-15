<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyCodeDefault extends AbstractMigration {

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
    public function change(): void {
        $stmt = $this->query('SELECT id,name FROM company'); // returns PDOStatement
        $rows = $stmt->fetchAll();
        foreach ($rows as $companyInfo) {
            $builder = $this->getQueryBuilder();
            $builder
                    ->update('company')
                    ->set('code', mb_substr(strtoupper(str_ireplace (' ', '_', $companyInfo['name'])), 0, 9))
                    ->where(['id' => $companyInfo['id']])
                    ->execute();
        }
    }
}
