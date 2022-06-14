<?php

/**
 * Multi Flexi  - Customer Select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of CompanySelect
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class CustomerSelect extends \Ease\Html\SelectTag {

    use \Ease\SQL\Orm;

    public function __construct($name, $defaultValue = null, $itemsIDs = false,
            $properties = array()) {
        parent::__construct($name, $this->loadItems(), $defaultValue, $properties);
    }

    /**
     * obtain Availble AbraFlexi servers
     * 
     * @return array
     */
    public function loadItems() {
        $customers = ['' => _('Choose customer')];
        $this->setMyTable('customer');
        $customersRaw = $this->getColumnsFromSQL(['id', 'firstname', 'lastname'], null, 'lastname');
        if (count($customersRaw)) {
            foreach ($customersRaw as $customer) {
                $customers[$customer['id']] = $customer['lastname'] . ' ' . $customer['firstname'];
            }
        }
        return $customers;
    }

}
