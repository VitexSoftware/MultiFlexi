<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class MultipleBinaries extends AbstractMigration
{

    public function change(): void
    {
        $table = $this->table('apps');
        $table->removeIndex(['executable'])->save();
    }
}
