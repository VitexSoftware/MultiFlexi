<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class ConfFieldRequied extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('conffield');
        $table
                ->addColumn('required', 'boolean', ['null' => false, 'default' => false])
                ->save();

    }
}
