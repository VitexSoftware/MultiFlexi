<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyName extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('company');
        $table->renameColumn('nazev', 'name')
                ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('company');
        $table->renameColumn('name', 'nazev')
                ->save();
    }
}
