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

final class CheckboxToBool extends AbstractMigration
{
    /**
     * Change Method.
     *
     * This migration updates the `conffield` table, changing all rows where the value is "checkbox" to "bool".
     */
    public function change(): void
    {
        $this->execute("UPDATE conffield SET type = 'file-path' WHERE type = 'directory'");
        
        $this->execute("UPDATE conffield SET type = 'file-path' WHERE type = 'file'");
        // Update 'checkbox' to 'bool'
        $this->execute("UPDATE conffield SET type = 'bool' WHERE type = 'checkbox'");

        // Update 'boolean' to 'bool'
        $this->execute("UPDATE conffield SET type = 'bool' WHERE type = 'boolean'");

        // Update 'text' to 'string'
        $this->execute("UPDATE conffield SET type = 'string' WHERE type = 'text'");

        // Update 'number' to 'integer'
        $this->execute("UPDATE conffield SET type = 'integer' WHERE type = 'number'");

        // Update 'select' to 'set'
        $this->execute("UPDATE conffield SET type = 'set' WHERE type = 'select'");
    }
}
