<?php

/**
 * Multi Flexi  - AbraFlexi server select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AbraFlexiSelect
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class AbraFlexiSelect extends \Ease\Html\SelectTag
{
    use \Ease\SQL\Orm;

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
        $abraflexis = ['' => _('Choose AbraFlexi server')];
        $this->setMyTable('abraflexis');
        $abraflexisRaw = $this->getColumnsFromSQL(['id', 'name'], null, 'name');
        foreach ($abraflexisRaw as $abraflexi) {
            $abraflexis[$abraflexi['id']] = $abraflexi['name'];
        }
        return $abraflexis;
    }
}
