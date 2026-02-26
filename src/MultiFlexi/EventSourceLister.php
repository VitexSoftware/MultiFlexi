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

namespace MultiFlexi;

/**
 * EventSourceLister for DataTable listing.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class EventSourceLister extends EventSource
{
    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'detailPage' => 'eventsource.php', 'valueColumn' => 'event_source.id', 'idColumn' => 'event_source.id'],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'adapter_type', 'type' => 'text', 'label' => _('Adapter Type')],
            ['name' => 'db_connection', 'type' => 'text', 'label' => _('DB Driver')],
            ['name' => 'db_host', 'type' => 'text', 'label' => _('DB Host')],
            ['name' => 'db_database', 'type' => 'text', 'label' => _('Database')],
            ['name' => 'enabled', 'type' => 'boolean', 'label' => _('Enabled')],
            ['name' => 'last_processed_id', 'type' => 'text', 'label' => _('Last Processed')],
        ]);
    }
}
