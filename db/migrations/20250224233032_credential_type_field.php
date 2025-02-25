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

final class CredentialTypeField extends AbstractMigration
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

        // Create the crtypefield table
        $table = $this->table('crtypefield');
        $table->addColumn('credential_type_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('keyname', 'string', ['limit' => 64, 'default' => null, 'null' => true])
            ->addColumn('type', 'string', ['limit' => 32, 'default' => null, 'null' => true])
            ->addColumn('description', 'string', ['limit' => 1024, 'default' => null, 'null' => true])
            ->addColumn('hint', 'string', ['limit' => 256, 'default' => null, 'null' => true])
            ->addColumn('defval', 'string', ['limit' => 256, 'default' => null, 'null' => true])
            ->addColumn('required', 'boolean', ['default' => 0, 'null' => false])
            ->addIndex(['credential_type_id', 'keyname'], ['unique' => true, 'name' => 'credtype_id'])
            ->addForeignKey('credential_type_id', 'credential_type', 'id', ['constraint' => 'cff-credtype_must_exist', 'delete' => 'CASCADE'])
            ->create();
    }
}
