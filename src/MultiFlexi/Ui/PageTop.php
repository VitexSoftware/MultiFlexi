<?php

/**
 * Multi Flexi  - Shared page top class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Ui\WebPage;

/**
 * Page TOP.
 */
class PageTop extends \Ease\Html\DivTag
{
    /**
     * Titulek stránky.
     *
     * @var string
     */
    public $pageTitle = null;

    /**
     * Nastavuje titulek.
     *
     * @param string $pageTitle
     */
    public function __construct($pageTitle = null)
    {
        parent::__construct();
        if (!is_null($pageTitle)) {
            WebPage::singleton()->setPageTitle($pageTitle);
        }
        WebPage::singleton()->body->addAsFirst(new MainMenu());
    }

    public function finalize()
    {
        return parent::finalize();
    }
}
