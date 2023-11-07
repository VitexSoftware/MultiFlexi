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
        return $appConfig;
    }
}
