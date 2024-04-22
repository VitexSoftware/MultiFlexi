<?php

declare(strict_types=1);

/**
 * Multi Flexi - Handle Loging Environment variables
 *
 * @author    Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2024 Vitex Software
 */

namespace MultiFlexi\Env;

/**
 * Description of Logger
 *
 * @author vitex
 */
class Logger extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys
     *
     * @return array
     */

    public static function allKeysHandled()
    {
        return ['EASE_LOGGER'];
    }
    
    /**
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        // If Zabbix action is enabled log only to syslog
        $actions = $this->engine->runTemplate->getPostActions();
        $methods[] = 'syslog';
        if(array_key_exists('Zabbix', $actions)){
            if(!($actions['Zabbix']['success'] || $actions['Zabbix']['fail'])){
                $methods[] = 'console';
            }
        } else {
            $methods[] = 'console';
        }
        return $this->addMetaData($this->addSelfAsSource(['EASE_LOGGER' => ['value' => implode('|', $methods)]]));
    }

    /**
     *
     * @return string
     */
    public static function name()
    {
        return _('Logger');
    }

    /**
     *
     * @return string
     */
    public static function description()
    {
        return _('Handle Logging for Ease Framework based Applications');
    }
}
