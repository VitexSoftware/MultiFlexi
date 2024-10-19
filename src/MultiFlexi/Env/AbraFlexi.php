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
 * Description of AbraFlexi.
 *
 * @deprecated since version 1.15.0.486
 *
 * @author vitex
 */
class AbraFlexi extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return [
            'ABRAFLEXI_URL',
            'ABRAFLEXI_LOGIN',
            'ABRAFLEXI_PASSWORD',
            'ABRAFLEXI_COMPANY',
        ];
    }

    public function getEnvironment(): array
    {
        $abraFlexiEnv = [];

        if ($this->engine->company->getDataValue('server')) {
            $server = new \MultiFlexi\Servers($this->engine->company->getDataValue('server'));

            if ($server->getDataValue('type') === 'AbraFlexi') {
                $platformHelper = new \MultiFlexi\AbraFlexi\Company($this->engine->company->getMyKey(), $server->getData());

                foreach ($platformHelper->getEnvironment() as $key => $value) {
                    $abraFlexiEnv[$key] = ['value' => $value];
                }
            }
        }

        return $this->addMetaData($this->addSelfAsSource($abraFlexiEnv));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return 'AbraFlexi';
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Provide Connection credentials for AbraFlexi');
    }
}
