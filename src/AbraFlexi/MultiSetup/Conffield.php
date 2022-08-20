<?php

/**
 * Multi Flexi - Configuration Flield Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

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
