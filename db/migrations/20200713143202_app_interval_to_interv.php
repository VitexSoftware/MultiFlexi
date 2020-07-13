<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AppIntervalToInterv extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up()
    {
        $table = $this->table('appcompany');
        $table->renameColumn('interval', 'interv')->save();
    }

    /**
     * Migrate Down.
     */
    public function down()
    {
        $table = $this->table('appcompany');
        $table->renameColumn('interv', 'interval')->save();
    }
}
