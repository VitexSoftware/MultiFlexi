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
class Application extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['MULTIFLEXI_APPLICATION_ID', 'MULTIFLEXI_APPLICATION_NAME', 'MULTIFLEXI_APPLICATION_UUID'];
    }

    /**
     * Application specific Environment.
     */
    public function getEnvironment(): \MultiFlexi\ConfigFields
    {
        $envApplication = new \MultiFlexi\ConfigFields(self::name());
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_APPLICATION_ID', 'integer'))->setValue((string) $this->engine->application->getMyKey()));
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_APPLICATION_NAME', 'string'))->setValue($this->engine->application->getDataValue('name')));
        $envApplication->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_APPLICATION_UUID', 'string'))->setValue($this->engine->application->getDataValue('uuid')));

        return $envApplication;
    }

    public static function name(): string
    {
        return _('Application');
    }

    public static function description(): string
    {
        return _('Provide per Application Custom environment');
    }
}
