<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AppNameDesc extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('apps');
        $table->renameColumn('nazev', 'name')
                ->renameColumn('popis', 'description')
                ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('apps');
        $table->renameColumn('name', 'nazev')
                ->renameColumn('description', 'popis')
                ->save();
    }
}
