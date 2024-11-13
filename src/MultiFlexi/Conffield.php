<?php

declare(strict_types=1);

/**
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi;

/**
 * Description of Conffield.
 *
 * @author vitex
 */
class Conffield extends \Ease\SQL\Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'conffield';
        parent::__construct($identifier, $options);
    }

    public function takeData($data)
    {
        $checked = false;
        unset($data['add']);

        if (\array_key_exists('app_id', $data)) {
            $checked = true;
        }

        if (\array_key_exists('id', $data) && ($data['id'] === '')) {
            unset($data['id']);
            $checked = true;
        }

        $data['required'] = \array_key_exists('required', $data) && $data['required'] === 'on' ? 1 : 0;

        return $checked ? parent::takeData($data) : 0;
    }

    /**
     * @param int $appId
     *
     * @return array
     */
    public function appConfigs($appId)
    {
        return Environmentor::addSource($this->getColumnsFromSQL(['*'], ['app_id' => $appId], 'keyname', 'keyname'), \get_class($this));
    }

    /**
     * Create new Environment field for an application.
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
        $this->setDataValue('defval', self::applyMarcros($envProperties['defval'], $envProperties));

        return $this->dbsync();
    }

    /**
     * Populate template by values from environment.
     */
    public static function applyMarcros(string $template, array $fields): string
    {
        foreach ($fields as $envKey => $envInfo) {
            $hydrated = array_key_exists('value', $envInfo) ? str_replace('{'.$envKey.'}', (string) $envInfo['value'], $template) : $template;
        }

        return $hydrated;
    }

    public static function getAppConfigs($appId)
    {
        return (new self())->appConfigs($appId);
    }
}
