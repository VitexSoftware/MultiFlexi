<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * Description of LaunchButton
 *
 * @author vitex
 */
class LaunchButton extends \Ease\TWB4\LinkButton {
    public function __construct($companyID, $code, $properties = []) {
        parent::__construct('launch.php?company=' . $companyID . '&id=' . $code, [ _('Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg' , _('Launch'),['height'=>'30px'])  ] , 'warning btn-lg btn-block ', $properties);
    }
}
