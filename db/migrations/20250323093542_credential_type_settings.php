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

final class CredentialTypeSettings extends AbstractMigration
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
        // Check if the database is MySQL
        $databaseType = $this->getAdapter()->getOption('adapter');
        $unsigned = ($databaseType === 'mysql') ? ['signed' => false] : [];

        // Create the credentials table
        $table = $this->table('credtypedata', ['comment' => 'CredentialType interanl settings']);
        $table->addColumn('credential_type_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('name', 'string', ['limit' => 255])
            ->addColumn('value', 'string', ['limit' => 255])
            ->addColumn('type', 'string', ['limit' => 255])
            ->addIndex(['credential_type_id', 'name'], ['unique' => true, 'name' => 'credtype_type_id'])
            ->addForeignKey('credential_type_id', 'credential_type', 'id', ['constraint' => 'ctff-credtype_must_exist', 'delete' => 'CASCADE'])
            ->create();
    }
}
