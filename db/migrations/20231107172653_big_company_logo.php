<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BigCompanyLogo extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('company');
        if ($this->adapter->getAdapterType() == 'mysql') {
            $table
                    ->changeColumn('logo', 'text', ['null' => false, 'limit' => Phinx\Db\Adapter\MysqlAdapter::TEXT_LONG])
                    ->update();
        }

    }
}
