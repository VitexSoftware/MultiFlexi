<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup\Ui;

/**
 * Description of DbStatus
 *
 * @author vitex
 */
class DbStatus extends \Ease\TWB4\Row {

    public function __construct() {
        parent::__construct();
        $flexiBees = (new \FlexiPeeHP\MultiSetup\FlexiBees())->listingQuery()->count();
        $customers = (new \FlexiPeeHP\MultiSetup\Customer())->listingQuery()->count();
        $companys = (new \FlexiPeeHP\MultiSetup\Company())->listingQuery()->count();

        $this->addColumn(4, new \Ease\Html\ButtonTag([_('Servers').'&nbsp;', new \Ease\TWB4\PillBadge('success', $flexiBees)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(4, new \Ease\Html\ButtonTag([_('Customers').'&nbsp;', new \Ease\TWB4\PillBadge('success', $customers)], ['class' => 'btn btn-default', 'type' => 'button']));
        $this->addColumn(4, new \Ease\Html\ButtonTag([_('Companys').'&nbsp;', new \Ease\TWB4\PillBadge('success', $companys)], ['class' => 'btn btn-default', 'type' => 'button']));
    }

}
