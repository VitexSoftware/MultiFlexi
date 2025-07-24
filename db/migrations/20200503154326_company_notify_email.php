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
class CompanyNotifyEmail extends AbstractMigration
{
    public function change(): void
    {
        $refTable = $this->table('company');
        $refTable->addColumn('email', 'string', ['null' => true, 'length' => 64])->save();
    }
}
