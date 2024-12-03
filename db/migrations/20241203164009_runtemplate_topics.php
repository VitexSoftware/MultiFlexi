<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class RuntemplateTopics extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('runtemplate_topics');
        $table->addColumn('runtemplate_id', 'integer')
              ->addColumn('topic_id', 'integer')
              ->addForeignKey('runtemplate_id', 'runtemplate', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->addForeignKey('topic_id', 'topic', 'id', ['delete'=> 'CASCADE', 'update'=> 'NO_ACTION'])
              ->create();
    }
}
