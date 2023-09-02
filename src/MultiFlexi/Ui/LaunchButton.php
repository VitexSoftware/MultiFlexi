<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of LaunchButton
 *
 * @author vitex
 */
class LaunchButton extends \Ease\TWB4\LinkButton {

    /**
     * 
     * @param int $appCompanyID
     * @param array $properties
     */
    public function __construct($appCompanyID, $properties = []) {
        parent::__construct('launch.php?id=' . $appCompanyID, [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg btn-block ', $properties);
    }

}
