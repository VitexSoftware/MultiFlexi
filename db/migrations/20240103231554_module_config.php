<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ModuleConfig extends AbstractMigration
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
        $table = $this->table('modconfig');
        $table->addColumn('name', 'string', ['comment' => 'Module per company configurations'])
            ->addColumn('module', 'string', ['comment' => 'Configuration belongs to'])
            ->addColumn('key', 'string', ['comment' => 'Configuration Key'])
            ->addColumn('value', 'string', ['comment' => 'Configuration Value'])
            ->addColumn('DatSave', 'datetime', ['null' => true])
            ->create();

    }
}
