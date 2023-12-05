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
        return ['MULTIFLEXI', 'MULTIFLEXI_JOB_ID'];
    }

    /**
     * MultiFlexi Related values
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        return $this->addSelfAsSource([
            'MULTIFLEXI' => ['value' => \Ease\Shared::appVersion()],
            'MULTIFLEXI_JOB_ID' => ['value' => $this->engine->getMyKey()]
                ]);
    }
}
