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

namespace MultiFlexi\Command;

/**
 * Description of Command.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
abstract class Command extends \Symfony\Component\Console\Command\Command
{
    public function listing(): array
    {
        return [];
    }

    public function outputTable(array $data): void
    {
        if ($data) {
            $table = new \LucidFrame\Console\ConsoleTable();

            foreach (array_keys(current($data))as $column) {
                $table->addHeader($column);
            }

            foreach ($data as $row) {
                $table->addRow($row);
            }

            $table->display();
            // TODO: https://github.com/phplucidframe/console-table/issues/14#issuecomment-2167643219
        } else {
            echo _('No data')."\n";
        }
    }
}
