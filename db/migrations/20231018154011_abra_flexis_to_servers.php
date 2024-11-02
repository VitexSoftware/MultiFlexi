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

final class AbraFlexisToServers extends AbstractMigration
{
    /**
     * Migrate Up.
     */
    public function up(): void
    {
        $table = $this->table('abraflexis');
        $table
            ->rename('servers')
            ->update();
    }

    /**
     * Migrate Down.
     */
    public function down(): void
    {
        $table = $this->table('servers');
        $table
            ->rename('servers')
            ->update();
    }
}
