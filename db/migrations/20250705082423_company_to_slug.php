<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class CompanyToSlug extends AbstractMigration
{
    public function change(): void
    {
        $this->table('company')->renameColumn('code', 'slug')->removeColumn('company')->removeColumn('rw')->removeColumn('webhook')->update();
    }
}
