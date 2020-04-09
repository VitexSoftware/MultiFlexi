<?php

/**
 * Multi FlexiBee Setup  - WebPage class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

/**
 * Description of WebPage
 *
 * @author vitex
 */
class WebPage extends \Ease\TWB4\WebPage {

    /**
     * Put page contents here
     * @var \Ease\TWB4\Container
     */
    public $container = null;

    public function __construct($pageTitle = null) {
        parent::__construct($pageTitle);
        $this->container = $this->addItem(new \Ease\TWB4\Container());
    }


}
