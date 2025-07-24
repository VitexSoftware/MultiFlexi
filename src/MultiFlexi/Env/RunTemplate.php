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
class RunTemplate extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     */
    public static function allKeysHandled(): array
    {
        return ['MULTIFLEXI_RUNTEMPLATE_ID', 'MULTIFLEXI_RUNTEMPLATE_NAME'];
    }

    /**
     * MultiFlexi Related values.
     */
    public function getEnvironment(): \MultiFlexi\ConfigFields
    {
        $envRuntemplate = new \MultiFlexi\ConfigFields(self::name());

        $envRuntemplate->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_RUNTEMPLATE_NAME', 'string'))->setValue($this->engine->runTemplate->getRecordName()));
        $envRuntemplate->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_RUNTEMPLATE_ID', 'integer'))->setValue((string) $this->engine->runTemplate->getMyKey()));

        $envRuntemplate->addFields($this->engine->runTemplate->getRuntemplateEnvironment());

        return $envRuntemplate;
    }

    public static function name(): string
    {
        return _('RunTemplate');
    }

    public static function description(): string
    {
        return _('Provide stored RunTemplate Environment');
    }
}
