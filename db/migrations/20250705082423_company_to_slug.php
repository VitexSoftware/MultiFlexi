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
final class CompanyToSlug extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('company');

        // Check if the 'code' column exists before renaming
        if ($table->hasColumn('code')) {
            $table->renameColumn('code', 'slug');
        }

        // Remove 'rw' column if it exists
        if ($table->hasColumn('rw')) {
            $table->removeColumn('rw');
        }

        // Remove 'webhook' column if it exists
        if ($table->hasColumn('webhook')) {
            $table->removeColumn('webhook');
        }

        $table->update();
    }
}
