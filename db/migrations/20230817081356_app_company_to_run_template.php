<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AppCompanyToRunTemplate extends AbstractMigration
{

    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('appcompany');
        $table
                ->rename('runtemplate')
                ->update();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('runtemplate');
        $table
                ->rename('appcompany')
                ->update();
    }
}
