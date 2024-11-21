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

final class JobScheduleInterval extends AbstractMigration
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
        if (method_exists($this, 'getQueryBuilder')) {
            $builder = $this->getQueryBuilder('update');
            $builder
                ->update('job')
                ->set('schedule', null)
                ->execute();
        } else {
            $this->execute('UPDATE job SET schedule=NULL');
        }

        $table = $this->table('job');
        $table
            ->addColumn('schedule_type', 'string', ['comment' => 'Job Schedule type', 'default' => null, 'null' => true])
            ->changeColumn('schedule', 'timestamp', ['null' => true, 'comment' => 'Job Schedule time'])
            ->update();
    }
}
