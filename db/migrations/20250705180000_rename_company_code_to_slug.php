<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

/**
 * Migration to rename 'code' column to 'slug' in the company table.
 */
class RenameCompanyCodeToSlug extends AbstractMigration
{
    public function change(): void
    {
        if ($this->table('company')->hasColumn('code')) {
            $this->table('company')
                ->renameColumn('code', 'slug')
                ->update();
        }
    }
}
