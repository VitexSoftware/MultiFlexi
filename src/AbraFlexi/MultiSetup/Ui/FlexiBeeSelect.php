<?php
/**
 * Multi FlexiBee Setup  - FlexiBee server select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;


/**
 * Description of FlexiBeeSelect
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class FlexiBeeSelect extends \Ease\Html\Select
{

    use \Ease\SQL\Orm;

    /**
     * FlexiBee chooser
     * 
     * @param string $name
     * @param string $defaultValue
     * @param array  $itemsIDs
     * @param array  $properties
     */
    public function __construct($name, $defaultValue = null, $itemsIDs = false,
                                $properties = array())
    {
        $items    = $this->loadItems();
        $itemsIDs = array_keys($items);
        $this->errorNumber = 0;
        parent::__construct($name, $items, end($itemsIDs), $itemsIDs,
            $properties);
    }

    /**
     * obtain Availble FlexiBee servers
     * 
     * @return array
     */
    public function loadItems()
    {
        $flexiBees    = ['' => _('Choose FlexiBee server')];
        $this->setMyTable('flexibees');
        $flexiBeesRaw = $this->getColumnsFromSQL(['id', 'name'], null, 'name');
        foreach ($flexiBeesRaw as $flexiBee) {
            $flexiBees[$flexiBee['id']] = $flexiBee['name'];
        }
        return $flexiBees;
    }
}
