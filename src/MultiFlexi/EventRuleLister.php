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
 * EventRuleLister for DataTable listing.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class EventRuleLister extends EventRule
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
                'detailPage' => 'eventrule.php', 'valueColumn' => 'event_rule.id', 'idColumn' => 'event_rule.id'],
            ['name' => 'event_source_id', 'type' => 'text', 'label' => _('Event Source')],
            ['name' => 'evidence', 'type' => 'text', 'label' => _('Evidence')],
            ['name' => 'operation', 'type' => 'text', 'label' => _('Operation')],
            ['name' => 'runtemplate_id', 'type' => 'text', 'label' => _('RunTemplate')],
            ['name' => 'priority', 'type' => 'text', 'label' => _('Priority')],
            ['name' => 'enabled', 'type' => 'boolean', 'label' => _('Enabled')],
        ]);
    }

    #[\Override]
    public function completeDataRow(array $dataRowRaw): array
    {
        $data = parent::completeDataRow($dataRowRaw);

        if (!empty($data['event_source_id'])) {
            $source = new EventSource((int) $data['event_source_id'], ['autoload' => true]);
            $data['event_source_id'] = (string) new \Ease\Html\ATag('eventsource.php?id='.$data['event_source_id'], $source->getRecordName());
        }

        if (!empty($data['runtemplate_id'])) {
            $data['runtemplate_id'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$data['runtemplate_id'], '#'.$data['runtemplate_id']);
        }

        return $data;
    }
}
