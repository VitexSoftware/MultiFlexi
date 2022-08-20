<?php

/**
 * Multi Flexi  - WebPage class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Container;
use Ease\TWB4\Badge;

/**
 * Description of Confiffields
 *
 * @author vitex
 */
class ConfigFieldsBadges extends Container {

    /**
     * 
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null) {
        parent::__construct();
        foreach ($content as $conf) {
            $this->addItem(new Badge(array_key_exists('state', $conf) ? $conf['state'] : 'secondary', $conf['type'] . ' ' . $conf['keyname']));
            $this->addItem(' ');
        }
    }

}
