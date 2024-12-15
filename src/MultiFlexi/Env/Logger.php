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
class Logger extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['EASE_LOGGER'];
    }

    public function getEnvironment(): array
    {
        // If Zabbix action is enabled log only to syslog
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

        return $this->addMetaData($this->addSelfAsSource(['EASE_LOGGER' => ['value' => implode('|', $methods)]]));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return _('Logger');
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Handle Logging for Ease Framework based Applications');
    }
}
