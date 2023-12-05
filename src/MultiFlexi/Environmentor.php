<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Environmentor
 *
 * @author vitex
 */
abstract class Environmentor
{
    /**
     *
     * @var Job
     */
    public $engine;

    /**
     *
     * @param Job $engine
     */
    public function __construct(Job $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Generate Environment for current Job
     *
     * @return array
     */
    public function compileModulesEnv()
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');
        $jobEnv = [];
        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\' . $injector;
            $jobEnv = array_merge($jobEnv, (new $injectorClass($this))->getEnvironment());
        }
        return $jobEnv;
    }

    /**
     * Add source to environment
     *
     * @param array $environmentRaw
     *
     * @return array
     */
    public function addSelfAsSource(array $environmentRaw)
    {
        return self::addSource($environmentRaw, get_class($this));
    }

    /**
     * Add source to environment
     *
     * @param array  $environmentFields EnvFields with info
     * @param string $source            Force its source name to
     *
     * @return array
     */
    public static function addSource(array $environmentFields, string $source)
    {
        foreach ($environmentFields as $key => $fieldInfo) {
            $environmentFields[$key]['source'] = $source;
        }
        return $environmentFields;
    }

    public function functionName($param)
    {
    }

    public static function queryModules(array $modulesToQuery)
    {
        $moduleEnv = [];
        $job = new Job();
        foreach ($modulesToQuery as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\' . $injector;
            if (class_exists($injectorClass, true)) {
                $moduleEnv = array_merge($moduleEnv, (new $injectorClass($job))->getEnvironment());
            } else {
                $job->addStatusMessage(sprintf(_('Query for Nonexistent module %s vars'), $injectorClass), 'warning');
            }
        }
        return $moduleEnv;
    }

    /**
     * Return only key=>value pairs
     *
     * @param array $envInfo
     *
     * @return array
     */
    public static function flatEnv(array $envInfo)
    {
        $env = [];
        foreach ($envInfo as $key => $envData) {
            $env[$key] = $envData['value'];
        }
        return $env;
    }
}
