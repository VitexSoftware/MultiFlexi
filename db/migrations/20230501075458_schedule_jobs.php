<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ScheduleJobs extends AbstractMigration
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
        $table = $this->table('schedule');
        $table
                ->addColumn('after', 'datetime')
                ->addColumn('companyapp', 'integer')
                ->create();
                if ($this->adapter->getAdapterType() != 'sqlite') {
                    $table
                        ->changeColumn('id', 'biginteger', ['identity' => true, 'signed' => false])
                        ->save();
                }
            }
}
