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

final class AppRequirements extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table('apps');
        $table
            ->addColumn('requirements', 'string', ['comment' => 'MultiFlexi Modules required by Application'])
            ->update();
    }
}
