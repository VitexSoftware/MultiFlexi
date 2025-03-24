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

namespace MultiFlexi\Ui;

/**
 * Description of Serverselect.
 *
 * @deprecated since version 1.20
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class ServerSelect extends \Ease\Html\SelectTag
{
    use \Ease\SQL\Orm;
    use \Ease\RecordKey;
    public $myTable = 'servers';

    /**
     * AbraFlexi chooser.
     *
     * @param string $name
     * @param string $defaultValue
     * @param array  $itemsIDs
     * @param array  $properties
     */
    public function __construct(
        $name,
        $defaultValue = null,
        $itemsIDs = false,
        $properties = [],
    ) {
        $items = $this->loadItems();
        $itemsIDs = array_keys($items);
        $this->errorNumber = 0;
        parent::__construct($name, $items, empty($itemsIDs) ? $defaultValue : end($itemsIDs), $properties);
    }

    /**
     * obtain Availble AbraFlexi servers.
     *
     * @return array
     */
    public function loadItems()
    {
        $servers = ['' => _('No server specified')];
        $this->setMyTable('servers');
        $serversRaw = $this->getColumnsFromSQL(['id', 'name'], null, 'name');

        foreach ($serversRaw as $abraflexi) {
            $servers[$abraflexi['id']] = $abraflexi['name'];
        }

        return $servers;
    }
}
