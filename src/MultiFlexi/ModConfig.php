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
 * Description of ModConfig.
 *
 * @author vitex
 */
class ModConfig extends Engine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'modconfig';
        parent::__construct($identifier, $options);
    }

    public function getConf($module, $key)
    {
        return $this->listingQuery()->where(['module' => $module, 'cfg' => $key])->select(['value'], true)->fetch();
    }

    public function getModuleConf($module)
    {
        $configs = [];

        foreach ($this->listingQuery()->where(['module' => $module])->select(['cfg', 'value'], true)->fetchAll('cfg') as $cfg) {
            $configs[$cfg['cfg']] = $cfg['value'];
        }

        return $configs;
    }

    /**
     * @return int
     */
    public function setModuleConf(string $module, array $configurations)
    {
        $result = 0;

        foreach ($configurations as $key => $value) {
            if ($this->setConf($module, $key, $value)) {
                ++$result;
            }
        }

        return $result;
    }

    /**
     * @param string $module
     * @param string $key
     * @param string $value
     *
     * @return int
     */
    public function setConf($module, $key, $value)
    {
        if ($this->listingQuery()->where(['module' => $module, 'cfg' => $key])->count()) {
            $result = $this->updateToSQL(['value' => $value], ['module' => $module, 'cfg' => $key]);
        } else {
            $result = $this->insertToSQL(['module' => $module, 'cfg' => $key, 'value' => $value]);
        }

        return $result;
    }

    /**
     * @return array
     */
    public function formData()
    {
        return [];
    }

    public function saveConfigForModules($configurations): void
    {
        foreach ($configurations as $module => $config) {
            $this->setModuleConf($module, $config);
        }
    }

    /**
     * Gather Configuration for multiple modules.
     *
     * @param array $modules list of modules
     *
     * @return array
     */
    public function getConfigForModules($modules)
    {
        $config = [];

        foreach ($modules as $class) {
            $baseClass = self::classBasename($class);
            $config[$baseClass] = $this->getModuleConf($baseClass);
        }

        return $config;
    }

    /**
     * Base for Class Name in namespace.
     *
     * @param string $class
     *
     * @return string
     */
    public static function classBasename($class)
    {
        return basename(str_replace('\\', '/', $class));
    }

    public function saveFormData($post): void
    {
        foreach ($post as $moduleName => $moduleData) {
            $this->setModuleConf($moduleName, $moduleData);
        }
    }
}
