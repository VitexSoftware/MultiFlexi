<?php

/**
 * Multi Flexi  - AbraFlexi server select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of Serverselect
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class ServerSelect extends \Ease\Html\SelectTag
{
    use \Ease\SQL\Orm;
    use \Ease\RecordKey;
    
    public $myTable = 'servers';


    /**
     * AbraFlexi chooser
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
        $properties = array()
    ) {
        $items = $this->loadItems();
        $itemsIDs = array_keys($items);
        $this->errorNumber = 0;
        parent::__construct($name, $items, empty($itemsIDs) ? $defaultValue : end($itemsIDs), $properties);
    }

    /**
     * obtain Availble AbraFlexi servers
     *
     * @return array
     */
    public function loadItems()
    {
        $servers = ['' => _('Choose AbraFlexi server')];
        $this->setMyTable('servers');
        $serversRaw = $this->getColumnsFromSQL(['id', 'name'], null, 'name');
        foreach ($serversRaw as $abraflexi) {
            $servers[$abraflexi['id']] = $abraflexi['name'];
        }
        return $servers;
    }
}
