<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

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
            ->addForeignKey('runtemplate_id', 'runtemplate', ['id'], ['constraint' => 'r2c_runtemplate_must_exist'])
            ->addForeignKey('topic_id', 'topic', ['id'], ['constraint' => 'r2t_topic_must_exist'] )
            ->addIndex(['runtemplate_id', 'topic_id'], ['unique' => true])
            ->create();
    }
}
