<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup;

/**
 * Description of Engine
 *
 * @author vitex
 */
class Engine extends \Ease\SQL\Engine {

    public function saveToSQL($data = null,$searchForID = false) {
        if (is_null($data)) {
            $data = $this->getData();
        }
        unset($data['class']);
        return parent::saveToSQL($data,$searchForID);
    }
    
}
