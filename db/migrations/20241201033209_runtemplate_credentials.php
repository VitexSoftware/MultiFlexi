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

final class RuntemplateCredentials extends AbstractMigration
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

        // Create the runtplcreds table
        $runtemplates = $this->table('runtplcreds');
        $runtemplates
            ->addColumn('runtemplate_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('credentials_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addForeignKey('runtemplate_id', 'runtemplate', ['id'], ['constraint' => 'r2c_runtemplate_must_exist'])
            ->addForeignKey('credentials_id', 'credentials', ['id'], ['constraint' => 'r2c_credential_must_exist'])
            ->addIndex(['runtemplate_id', 'credentials_id'], ['unique' => true])
            ->create();
    }
}
