<?php

declare(strict_types=1);

/**
 * Multi Flexi - Handle MultiFlexi Environment variables
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
class MultiFlexi extends \MultiFlexi\Environmentor implements Injector
{
    /**
     * List of all known keys
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['MULTIFLEXI', 'JOB_ID'];
    }

    /**
     * MultiFlexi Related values
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        return $this->addMetaData($this->addSelfAsSource([
            'MULTIFLEXI' => ['value' => \Ease\Shared::appVersion()],
            'JOB_ID' => ['value' => $this->engine->getMyKey()]
        ]));
    }

    /**
     *
     * @return string
     */
    public static function name()
    {
        return _('MultiFlexi');
    }

    /**
     *
     * @return string
     */
    public static function description()
    {
        return _('Provide Informations about Current running Environment');
    }
}
