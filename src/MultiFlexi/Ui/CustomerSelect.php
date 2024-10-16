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
 * Description of CompanySelect.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class CustomerSelect extends \Ease\Html\SelectTag
{
    use \Ease\SQL\Orm;
    use \Ease\RecordKey;
    public $myTable = 'customers';

    public function __construct(
        $name,
        $defaultValue = '',
        $itemsIDs = false,
        $properties = []
    ) {
        parent::__construct($name, $this->loadItems(), $defaultValue, $properties);
    }

    /**
     * obtain Available AbraFlexi servers.
     *
     * @return array
     */
    public function loadItems()
    {
        $customers = ['' => _('Choose customer')];
        $this->setMyTable('customer');
        $customersRaw = $this->getColumnsFromSQL(['id', 'firstname', 'lastname'], null, 'lastname');

        if (\count($customersRaw)) {
            foreach ($customersRaw as $customer) {
                $customers[$customer['id']] = $customer['lastname'].' '.$customer['firstname'];
            }
        }

        return $customers;
    }
}
