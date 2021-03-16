<?php

/**
 * Multi AbraFlexi Setup  - Database use overview
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of DbStatus
 *
 * @author vitex
 */
class DbStatus extends \Ease\TWB4\Row {

    /**
     * Show status of database
     */
    public function __construct() {
        parent::__construct();
        $abraflexis = (string) (new \AbraFlexi\MultiSetup\AbraFlexis())->listingQuery()->count();
        $customers = (string) (new \AbraFlexi\MultiSetup\Customer())->listingQuery()->count();
        $companys = (string) (new \AbraFlexi\MultiSetup\Company())->listingQuery()->count();
        $apps = (string) (new \AbraFlexi\MultiSetup\Application())->listingQuery()->count();
        $assigned = (string) (new \AbraFlexi\MultiSetup\AppToCompany())->listingQuery()->count();

        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Apps') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $apps)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Servers') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $abraflexis)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Customers') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $customers)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Companies') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $companys)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Assigned') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $assigned)], ['class' => 'btn btn-default', 'type' => 'button']));
    }

}
