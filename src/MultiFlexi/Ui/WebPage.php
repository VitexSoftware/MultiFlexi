<?php

/**
 * Multi Flexi  - WebPage class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of WebPage
 *
 * @author vitex
 */
class WebPage extends \Ease\TWB4\WebPage
{
    /**
     * Put page contents here
     * @var \Ease\TWB4\Container
     */
    public $container = null;

    /**
     * Current Customer
     * @var \MultiFlexi\Customer
     */
    public $customer = null;

    /**
     *
     * @param string $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct($pageTitle);
        $this->container = $this->addItem(new \Ease\TWB4\Container());
        $this->container->setTagClass('container-fluid');
        $this->addCSS('
');
    }
}
