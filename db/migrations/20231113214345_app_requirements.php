<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AppRequirements extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('apps');
        $table
                ->addColumn('requirements', 'string', ['comment' => 'MultiFlexi Modules required by Application'])
                ->update();

    }
}
