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

namespace MultiFlexi\Env;

/**
 * Description of Logger.
 *
 * @author vitex
 */
class Application extends \MultiFlexi\Environmentor implements injector {

    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled() {
        return [];
    }

    /**
     * Generate Environment for current Job.
     *
     * @return array
     */
    public function compileEnv() {
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
     * Obtain Environment to configure application.
     */
    public function getEnvironment(): array {
        $customConfig = new \MultiFlexi\Configuration();
        $appConfig = [];

        foreach ($customConfig->getAppConfig($this->engine->company->getMyKey(), $this->engine->application->getMyKey()) as $cfg) {
            $appConfig[$cfg['name']]['value'] = $cfg['value'];
        }

        $appConfigs = \MultiFlexi\Conffield::getAppConfigs($this->engine->application->getMyKey());
        if (\array_key_exists($this->engine->application->getDataValue('resultfile'), $appConfigs)) {
            $appConfig[$this->engine->application->getDataValue('resultfile')]['value'] = sys_get_temp_dir() . '/' . \Ease\Functions::randomString(10);
        }

        return $this->addMetaData($this->addSelfAsSource($appConfig));
    }

    /**
     * @return string
     */
    public static function name() {
        return _('Application');
    }

    /**
     * @return string
     */
    public static function description() {
        return _('Provide per Application Custom environment');
    }
}
