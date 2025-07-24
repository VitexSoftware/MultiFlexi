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
     * Application specific Environment.
     */
    public function getEnvironment(): \MultiFlexi\ConfigFields
    {
        $envApplication = new \MultiFlexi\ConfigFields(self::name());
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_JOB_ID', 'integer'))->setValue((string) $this->engine->getMyKey()));
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_EXECUTOR', 'string'))->setValue($this->engine->getDataValue('executor')));
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_VERSION', 'string'))->setValue(\Ease\Shared::appVersion()));

        return $envApplication;
    }

    public static function name(): string
    {
        return _('MultiFlexi');
    }

    public static function description(): string
    {
        return _('Provide Informations about Current running Environment');
    }
}
