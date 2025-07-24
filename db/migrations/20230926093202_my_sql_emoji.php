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
        $adapterType = $this->adapter->getAdapterType();

        if ($adapterType === 'mysql') {
            $this->execute('ALTER DATABASE multiflexi CHARACTER SET = utf8mb4 COLLATE = utf8mb4_unicode_ci;');
            $this->execute('ALTER TABLE job CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
            $this->execute('ALTER TABLE log CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
        }
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
    }
}
