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
class ConfigRegistry extends AbstractMigration
{
    public function change(): void
    {
        $databaseType = $this->getAdapter()->getOption('adapter');
        $unsigned = ($databaseType === 'mysql') ? ['signed' => false] : [];

        $customFields = $this->table('conffield');
        $customFields->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('keyname', 'string', ['length' => 64])
            ->addColumn('type', 'string', ['length' => 32])
            ->addColumn('description', 'string', ['length' => 1024])
            ->addIndex(['app_id', 'keyname'], ['unique' => true])
            ->addForeignKey('app_id', 'apps', 'id', ['constraint' => 'cff-app_must_exist', 'delete' => 'CASCADE']);
        $customFields->create();

        $configs = $this->table('configuration');
        $configs->addColumn('app_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('company_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addColumn('key', 'string', ['length' => 64])
            ->addColumn('value', 'string', ['length' => 1024])
            ->addColumn('runtemplate_id', 'integer', array_merge(['null' => false], $unsigned))
            ->addIndex(['app_id', 'company_id'])
            ->addIndex(['runtemplate_id', 'key'], ['unique' => true])
            ->addForeignKey('app_id', 'apps', ['id'], ['constraint' => 'cfg-app_must_exist', 'delete' => 'CASCADE'])
            ->addForeignKey('company_id', 'company', ['id'], ['constraint' => 'cfg-company_must_exist', 'delete' => 'CASCADE']);
        $configs->create();
    }
}
