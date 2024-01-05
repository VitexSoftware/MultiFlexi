<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ModuleConfigKey extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('modconfig');
        $table->renameColumn('key', 'cfg')
              ->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('modconfig');
        $table->renameColumn('cfg', 'key')
               ->save();
    }
    
}
