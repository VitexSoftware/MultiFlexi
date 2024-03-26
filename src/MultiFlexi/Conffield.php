<?php

/**
 * Multi Flexi - Configuration Flield Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2024 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Conffield
 *
 * @author vitex
 */
class Conffield extends \Ease\SQL\Engine
{
    public $myTable = 'conffield';

    public function takeData($data)
    {
        $checked = false;
        unset($data['add']);
        if (array_key_exists('app_id', $data)) {
            $checked = true;
        }
        if (array_key_exists('id', $data) && ($data['id'] == '')) {
            unset($data['id']);
            $checked = true;
        }
        
        $data['required'] = array_key_exists('required', $data) && $data['required'] == 'on' ? 1 : 0; 
        return $checked ? parent::takeData($data) : 0;
    }

    /**
     *
     * @param int $appId
     *
     * @return array
     */
    public function appConfigs($appId)
    {
        return Environmentor::addSource($this->getColumnsFromSQL(['*'], ['app_id' => $appId], 'keyname', 'keyname'), get_class($this));
    }

    /**
     * Create new Environment field for an application
     *
     * @param int    $appId
     * @param string $envName
     * @param array  $envProperties
     */
    public function addAppConfig($appId, $envName, $envProperties)
    {
        $this->dataReset();

        $candidat = $this->listingQuery()->where('app_id', $appId)->where('keyname', $envName);
        if (!empty($candidat)) {
            $currentData = $candidat->fetch();
            if ($currentData) {
                $this->setMyKey($currentData['id']);
            }
        }

        $this->setDataValue('app_id', $appId);
        $this->setDataValue('keyname', $envName);

        $this->setDataValue('type', $envProperties['type']);
        $this->setDataValue('description', $envProperties['description']);
        $this->setDataValue('defval', $envProperties['defval']);
        return $this->dbsync();
    }

    public static function getAppConfigs($appId)
    {
        return (new self())->appConfigs($appId);
    }
}
