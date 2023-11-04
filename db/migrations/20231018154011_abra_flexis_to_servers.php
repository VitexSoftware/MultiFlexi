<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AbraFlexisToServers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('abraflexis');
        $table
            ->rename('servers')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('servers');
        $table
            ->rename('servers')
            ->update();
    }    
    
}
