<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyAppToRunTemplate extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('schedule');
        $table->renameColumn('companyapp', 'job')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('schedule');
        $table->renameColumn('job', 'companyapp')
               ->save();
    }

}
