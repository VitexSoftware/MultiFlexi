<?php

/**
 * Multi Flexi  - Database use overview
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

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
        $abraflexis = (string) (new \MultiFlexi\AbraFlexis())->listingQuery()->count();
        $customers = (string) (new \MultiFlexi\Customer())->listingQuery()->count();
        $companys = (string) (new \MultiFlexi\Company())->listingQuery()->count();
        $apps = (string) (new \MultiFlexi\Application())->listingQuery()->count();
        $assigned = (string) (new \MultiFlexi\RunTemplate())->listingQuery()->count();

        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Apps') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $apps)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Servers') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $abraflexis)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Customers') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $customers)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Companies') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $companys)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(2, new \Ease\Html\ButtonTag([_('Assigned') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $assigned)], ['class' => 'btn btn-default', 'type' => 'button']));
    }

}
