<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace FlexiPeeHP\MultiSetup;

/**
 * Description of Conffield
 *
 * @author vitex
 */
class Conffield extends \Ease\SQL\Engine {

    public $myTable = 'conffield';

    public function takeData($data) {
        $checked = false;
        unset($data['add']);
        if (array_key_exists('app_id', $data)) {
            $checked = true;
        }
        if (array_key_exists('id', $data) && ($data['id'] == '')) {
            unset($data['id']);
            $checked = true;
        }
        return $checked ? parent::takeData($data) : 0;
    }

    /**
     * 
     * @param int $appId
     * 
     * @return array
     */
    public function appConfigs($appId) {
        return $this->getColumnsFromSQL(['*'], ['app_id' => $appId], 'keyname', 'keyname');
    }

    public static function getAppConfigs($appId) {
        return (new self)->appConfigs($appId);
    }
    
}
