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
class MultiFlexi extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['MULTIFLEXI_VERSION', 'MULTIFLEXI_JOB_ID', 'MULTIFLEXI_EXECUTOR'];
    }

    /**
     * MultiFlexi Related values.
     */
    public function getEnvironment(): array
    {
        return $this->addMetaData($this->addSelfAsSource([
            'MULTIFLEXI_VERSION' => ['value' => \Ease\Shared::appVersion()],
            'MULTIFLEXI_JOB_ID' => ['value' => $this->engine->getMyKey()],
            'MULTIFLEXI_EXECUTOR' => ['value' => $this->engine->getDataValue('executor')],
        ]));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return _('MultiFlexi');
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Provide Informations about Current running Environment');
    }
}
