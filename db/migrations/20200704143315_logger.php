<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class Logger extends AbstractMigration {

    /**
     */
    public function change(): void {
        // create the table
        $table = $this->table('log');
        $table->addColumn('company_id', 'integer', ['null' => true, 'comment' => 'applied to company'])
                ->addColumn('apps_id', 'integer', ['null' => true, 'comment' => 'application used'])
                ->addColumn('user_id', 'integer', ['null' => true, 'comment' => 'signed user'])
                ->addColumn('severity', 'string', ['comment' => 'message type'])
                ->addColumn('venue', 'string', ['comment' => 'message producer'])
                ->addColumn('message', 'text', ['comment' => 'main text'])
                ->addColumn('created', 'timestamp', ['default' => 'CURRENT_TIMESTAMP'])
                ->addIndex(['apps_id', 'company_id'],  ['unique' => true])
                ->addIndex('user_id')
                ->create();
    }

}
