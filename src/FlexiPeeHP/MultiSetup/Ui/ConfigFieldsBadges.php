<?php
/**
 * Multi FlexiBee Setup  - WebPage class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;


/**
 * Description of Confiffields
 *
 * @author vitex
 */
class ConfigFieldsBadges extends \Ease\Container {
    public function __construct($content = null, $properties = array()) {
        parent::__construct(null, $properties);
        foreach ($content as $conf){
            $this->addItem( new \Ease\TWB4\Badge('success',$conf['type'] . ' '. $conf['keyname']) );
        }
    }
}
