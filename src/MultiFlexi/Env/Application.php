<?php

declare(strict_types=1);

/**
 * Multi Flexi - Handle Application Environment variables
 *
 * @author    Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Logger
 *
 * @author vitex
 */
class Application extends \MultiFlexi\Environmentor implements Injector
{
    /**
     * List of all known keys
     *
     * @return array
     */

    public static function allKeysHandled()
    {
        return [];
    }

    /**
     * Generate Environment for current Job
     *
     * @return array
     */
    public function compileEnv()
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
     * Obtain Environment to configure application
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        $customConfig = new \MultiFlexi\Configuration();
        $appConfig = [];
        foreach ($customConfig->getAppConfig($this->engine->company->getMyKey(), $this->engine->application->getMyKey()) as $cfg) {
            $appConfig[$cfg['name']] = $cfg['value'];
        }
        return $this->addMetaData($this->addSelfAsSource($appConfig));
    }
    
    /**
     * 
     * @return string
     */
    public static function name(){
        return _('Application');
    }
    
    /**
     * 
     * @return string
     */
    public static function description(){
        return _('Provide per Application Custom environment');
    }
    
}
