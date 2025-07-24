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

/**
 * @no-named-arguments
 */
final class UpdateCredata extends AbstractMigration
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
        $this->execute("UPDATE credata SET type = 'file-path' WHERE type = 'file'");
        // Update 'checkbox' to 'bool'
        $this->execute("UPDATE credata SET type = 'bool' WHERE type = 'checkbox'");

        // Update 'boolean' to 'bool'
        $this->execute("UPDATE credata SET type = 'bool' WHERE type = 'boolean'");

        // Update 'text' to 'string'
        $this->execute("UPDATE credata SET type = 'string' WHERE type = 'text'");

        // Update 'number' to 'integer'
        $this->execute("UPDATE credata SET type = 'integer' WHERE type = 'number'");

        // Update 'select' to 'set'
        $this->execute("UPDATE credata SET type = 'set' WHERE type = 'select'");
    }
}
