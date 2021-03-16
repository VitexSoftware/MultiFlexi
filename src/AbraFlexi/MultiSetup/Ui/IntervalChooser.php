<?php

/**
 * Multi AbraFlexi Setup  - Run interval select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiSetup\Ui;

/**
 * Description of IntervalChooser
 *
 * @author vitex
 */
class IntervalChooser extends \Ease\Html\SelectTag {

    /**
     * Script run interval chooser
     * 
     * @param string $name
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $defaultValue = null, $properties = array()) {
        parent::__construct($name, [
            'n' => _('Disabled'),
            'y' => _('Yearly'),
            'm' => _('Monthly'),
            'w' => _('Weekly'),
            'd' => _('Daily'),
            'h' => _('Hourly')
                ], $defaultValue, $properties);
    }

}
