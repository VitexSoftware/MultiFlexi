<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MySqlEmoji extends AbstractMigration
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
    public function up(): void
    {
        if ($this->adapter->getAdapterType() != 'mysql') {
            $this->execute('ALTER DATABASE multiflexi CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;');
            $this->execute('ALTER TABLE job CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            $this->execute('ALTER TABLE log CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        }

    }
    
    
    /**
     * Migrate Down.
     */
    public function down()
    {

    }
    
}
