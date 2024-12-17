<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MySQLutf8mb4 extends AbstractMigration
{
    public function change(): void
    {
        // Check if the database is MySQL
        $databaseType = $this->getAdapter()->getOption('adapter');
        if ($databaseType === 'mysql') {
            // Apply utf8mb4 character set to the database
            $this->execute("ALTER DATABASE `" . $this->getAdapter()->getOption('name') . "` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci");

            // Apply utf8mb4 character set to all tables
            $tables = $this->fetchAll("SHOW TABLES");
            foreach ($tables as $table) {
                $tableName = $table[array_keys($table)[0]];
                $this->execute("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }
    }
}
