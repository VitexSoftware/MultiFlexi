<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * Description of Breadcrumb
 *
 * @author vitex
 */
class Breadcrumb extends \Ease\TWB4\Breadcrumb {
    public function __construct($content = null, $properties = []) {
        parent::__construct( $content, $properties);
        /**
         * @var \AbraFlexi\MultiFlexi\Ui\WebPage Description
         */
        $oPage = WebPage::singleton();
        if(empty($oPage->customer)){
            $this->addPage('Choose Customer', 'customers.php');
        } else {
            $this->addPage(_('Customer').': '.$oPage->customer->getUserName(), $oPage->customer->getLink());
        }
//        $this->addCurrentPage('Service');
    }
}
