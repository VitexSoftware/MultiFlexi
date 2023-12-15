<?php

declare(strict_types=1);

/**
 * Multi Flexi - Handle Loging Environment variables
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
class Logger extends \MultiFlexi\Environmentor implements Injector
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
        return $this->addMetaData($this->addSelfAsSource(['EASE_LOGGER' => ['value' => 'syslog|console']]));
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
