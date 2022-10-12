<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

/**
 * Description of Token
 *
 * @author vitex
 */
class Token extends Engine {

    /**
     * Token live here
     * @var string
     */
    public $nameColumn = 'token';
    
    /**
     * We work with table token
     * @var string
     */
    public $myTable = 'token';
    
    
    public $createColumn = 'start';
    /**
     * 
     * @return \AbraFlexi\MultiFlexi\User
     */
    public function getUser() {
        return $this->getDataValue('user') ? new \AbraFlexi\MultiFlexi\User($this->getDataValue('user')) : null;
    }

    /**
     * Generate New Token
     * 
     * @return $this
     */
    public function generate() {
        $this->setDataValue($this->nameColumn, \Ease\Functions::randomString(20));
        return $this;
    }

}
