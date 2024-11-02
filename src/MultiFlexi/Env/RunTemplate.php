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
class RunTemplate extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['MULTIFLEXI_RUNTEMPLATE_ID', 'MULTIFLEXI_RUNTEMPLATE_NAME'];
    }

    /**
     * MultiFlexi Related values.
     */
    public function getEnvironment(): array
    {
        $envRuntemplate = [
            'MULTIFLEXI_RUNTEMPLATE_NAME' => ['value' => $this->engine->runTemplate->getRecordName()],
            'MULTIFLEXI_RUNTEMPLATE_ID' => ['value' => $this->engine->runTemplate->getMyKey()],
        ];

        return array_merge($this->addSelfAsSource($envRuntemplate), $this->addMetaData($this->engine->runTemplate->getRuntemplateEnvironment()));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return _('RunTemplate');
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Provide stored RunTemplate Environment');
    }
}
