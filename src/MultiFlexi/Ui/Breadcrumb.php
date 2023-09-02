<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of Breadcrumb
 *
 * @author vitex
 */
class Breadcrumb extends \Ease\TWB4\Breadcrumb {
    public function __construct($content = null, $properties = []) {
        parent::__construct( $content, $properties);
        /**
         * @var \MultiFlexi\Ui\WebPage Description
         */

        if(empty($_SESSION['customer'])){
            $this->addPage(_('choose Customer'), 'customers.php');
        } else {
            $this->addPage(_('Customer').': '.$_SESSION['customer']->getUserName(), $_SESSION['customer']->getLink());
        }
        
        if(empty($_SESSION['server'])){
            $this->addPage(_('choose Server'), 'abraflexis.php');
        } else {
            $this->addPage(_('Server').': '.$_SESSION['server']->getRecordName(), $_SESSION['server']->getLink());
        }
        
        if(empty($_SESSION['company'])){
            $this->addPage(_('choose Company'), 'companies.php');
        } else {
            $this->addPage(_('Company').': '.$_SESSION['company']->getRecordName(), $_SESSION['company']->getLink());
        }
        
        
//        $this->addCurrentPage('Service');
    }
}
