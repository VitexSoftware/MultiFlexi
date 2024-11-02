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
 * Description of Environmentor.
 *
 * @author vitex
 */
abstract class Environmentor
{
    public Job $engine;

    public function __construct(Job $engine)
    {
        $this->engine = $engine;
    }

    /**
     * Generate Environment for current Job.
     *
     * @return array
     */
    public function compileModulesEnv()
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');
        $jobEnv = [];

        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\'.$injector;
            $jobEnv = array_merge($jobEnv, (new $injectorClass($this))->getEnvironment());
        }

        return $jobEnv;
    }

    /**
     * Add MetaData to Environment Fields.
     */
    public function addMetaData(array $environment): array
    {
        foreach ($this->engine->application->getAppEnvironmentFields() as $key => $envMeta) {
            if (\array_key_exists($key, $environment)) {
                foreach ($envMeta as $mKey => $mValue) {
                    if (\array_key_exists($mKey, $environment[$key]) === false) {
                        $environment[$key][$mKey] = $mValue;
                    }
                }
            }
        }

        return $environment;
    }

    /**
     * Add source to environment.
     *
     * @return array
     */
    public function addSelfAsSource(array $environmentRaw)
    {
        return self::addSource($environmentRaw, \get_class($this));
    }

    /**
     * Add source to environment.
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

    public static function queryModules(array $modulesToQuery)
    {
        $moduleEnv = [];
        $job = new Job();

        foreach ($modulesToQuery as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\'.$injector;

            if (class_exists($injectorClass, true)) {
                $moduleEnv = array_merge($moduleEnv, (new $injectorClass($job))->getEnvironment());
            } else {
                $job->addStatusMessage(sprintf(_('Query for Nonexistent module %s vars'), $injectorClass), 'warning');
            }
        }

        return $moduleEnv;
    }

    /**
     * Return only key=>value pairs.
     */
    public static function flatEnv(array $envInfo): array
    {
        $env = [];

        foreach ($envInfo as $key => $envData) {
            $env[$key] = $envData['value'];
        }

        return $env;
    }

    public function setEnvironment(): void
    {
        $cmp = new Company((int) $this->getDataValue('company_id'));
        $cmp->setEnvironment();
        $envNames = [
            'EASE_EMAILTO' => $this->getDataValue('email'),
            'EASE_LOGGER' => $this->getDataValue('email') ? 'console|email' : 'console',
        ];
        $this->exportEnv($envNames);
        $customConfig = new Configuration();
        $customConfig->setEnvironment($cmp->getMyKey(), $app->getMyKey());
        $exec = $app->getDataValue('setup');
        $cmp->addStatusMessage('setup begin'.$exec.'@'.$cmp->getDataValue('name'));
        $cmp->addStatusMessage(shell_exec($exec), 'debug');
        $cmp->addStatusMessage('setu end'.$exec.'@'.$cmp->getDataValue('name'));
    }

    /**
     * Export given environment.
     */
    public function exportEnv(array $env): void
    {
        foreach ($env as $envName => $sqlValue) {
            $this->addStatusMessage(sprintf(_('Setting Environment %s to %s'), $envName, $sqlValue), 'debug');
            putenv($envName.'='.$sqlValue);
        }
    }
}
