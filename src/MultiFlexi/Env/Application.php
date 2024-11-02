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
     * Generate Environment for current Job.
     *
     * @return array
     */
    public function compileEnv()
    {
        return [
            'MULTIFLEXI_APPLICATION_ID' => ['value' => $this->engine->application->getMyKey()],
            'MULTIFLEXI_APPLICATION_NAME' => ['value' => $this->engine->application->getDataValue('name')],
            'MULTIFLEXI_APPLICATION_UUID' => ['value' => $this->engine->application->getDataValue('uuid')],
        ];
    }

    /**
     * Obtain Environment to configure application.
     */
    public function getEnvironment(): array
    {
        return $this->addMetaData($this->addSelfAsSource($this->compileEnv()));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return _('Application');
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Provide per Application Custom environment');
    }
}
