<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Phinx\Migration\AbstractMigration;

/**
 * @no-named-arguments
 */
final class MySQLutf8mb4 extends AbstractMigration
{
    public function change(): void
    {
        // Check if the database is MySQL
        $databaseType = $this->getAdapter()->getOption('adapter');

        if ($databaseType === 'mysql') {
            // Apply utf8mb4 character set to the database
            $this->execute('ALTER DATABASE `'.$this->getAdapter()->getOption('name').'` CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci');

            // Apply utf8mb4 character set to all tables
            $tables = $this->fetchAll('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = $table[array_keys($table)[0]];
                $this->execute("ALTER TABLE `{$tableName}` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }
        }
    }
}
