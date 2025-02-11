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

final class CredentialTypeID extends AbstractMigration
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
        $databaseType = $this->getAdapter()->getOption('adapter');
        $unsigned = ($databaseType === 'mysql') ? ['signed' => false] : [];

        // Adding 'credential_type_id' column to 'credentials' table
        $table = $this->table('credentials');
        $table->addColumn('credential_type_id', 'integer', array_merge(['null' => false], $unsigned))
            ->update();

        // Check if the 'credentials' table is empty
        $rowCount = $this->fetchRow('SELECT COUNT(*) as count FROM credentials')['count'];

        if ($rowCount === 0) {
            // Add foreign key constraint only if the table is empty
            $table->addForeignKey('credential_type_id', 'credential_type', ['id'], ['constraint' => 'ct2c_credential_type_must_exist'])
                ->update();
        }
    }
}
