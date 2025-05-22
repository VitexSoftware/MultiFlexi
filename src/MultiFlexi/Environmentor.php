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
