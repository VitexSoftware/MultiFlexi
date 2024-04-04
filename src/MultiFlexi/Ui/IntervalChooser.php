<?php

/**
 * Multi Flexi  - Run interval select
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of IntervalChooser
 *
 * @author vitex
 */
class IntervalChooser extends \Ease\Html\SelectTag
{

    /**
     * Script run interval chooser
     *
     * @param string $name
     * @param string $defaultValue
     * @param array  $properties
     */
    public function __construct($name, $defaultValue = null, $properties = array())
    {
        parent::__construct($name, [
            'n' => _('Disabled'),
            'i' => _('Minutly'),
            'h' => _('Hourly'),
            'd' => _('Daily'),
            'w' => _('Weekly'),
            'm' => _('Monthly'),
            'y' => _('Yearly'),
                ], $defaultValue, $properties);
    }
}
