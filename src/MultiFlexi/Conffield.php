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
 * Description of Conf field.
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

    #[\Override]
    public function takeData(array $data): int
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
     * @deprecated since version 1.27 Use the addConfigFields() instead
     *
     * @param int $appId
     */
    public function appConfigs($appId): array
    {
        return $this->getColumnsFromSQL(['*'], ['app_id' => $appId], 'keyname', 'keyname');
    }

    public function addConfigFields(\MultiFlexi\Application $app): ConfigFields
    {
        $confields = new ConfigFields($app->getDataValue('name'));

        foreach ($this->appConfigs($app->getMyKey()) as $configFieldData) {
            $field = new \MultiFlexi\ConfigField($code, $type, $name, $description, $hint);
            $confields->addField($field);
        }

        return $confields;
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
        $this->setDataValue('defval', \array_key_exists('defval', $envProperties) ? $envProperties['defval'] : '');

        return $this->dbsync();
    }

    public static function getAppConfigs(Application $app): ConfigFields
    {
        $appConfiguration = new ConfigFields(_('Application Config Fields'));

        foreach ((new self())->appConfigs($app->getMyKey()) as $appConfig) {
            $field = new ConfigField($appConfig['keyname'], self::fixType($appConfig['type']), $appConfig['keyname'], $appConfig['description']);
            $field->setRequired($appConfig['required'] === 1)->setDefaultValue($appConfig['defval'])->setSource(serialize($app));
            $appConfiguration->addField($field);
        }

        return $appConfiguration;
    }

    /**
     * Fix Old types to new.
     */
    public static function fixType(string $typeOld): string
    {
        return str_replace(
            ['directory', 'checkbox', 'boolean', 'switch', 'text', 'number', 'select'],
            ['file-path', 'bool', 'bool', 'bool', 'string', 'integer', 'set'],
            $typeOld,
        );
    }
}
