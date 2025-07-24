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
 *
 * @no-named-arguments
 */
class EaseLogger extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     */
    public static function allKeysHandled(): array
    {
        return ['EASE_LOGGER'];
    }

    /**
     * EaseLogger Related values.
     */
    public function getEnvironment(): \MultiFlexi\ConfigFields
    {
        $envEaseLogger = new \MultiFlexi\ConfigFields(self::name());

        $actions = $this->engine->runTemplate->getPostActions();
        $methods[] = 'syslog';
        $methods[] = 'console';

        if (\array_key_exists('Zabbix', $actions)) {
            if (!($actions['Zabbix']['success'] || $actions['Zabbix']['fail'])) {
                $methods[] = 'console';
            }
        } else {
            $methods[] = 'console';
        }

        $envEaseLogger->addField((new \MultiFlexi\ConfigField('EASE_LOGGER', 'string'))->setValue(implode('|', $methods)));

        return $envEaseLogger;
    }

    public static function name(): string
    {
        return _('Logger');
    }

    public static function description(): string
    {
        return _('Handle Logging for Ease Framework based Applications');
    }
}
