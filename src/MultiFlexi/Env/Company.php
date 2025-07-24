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
 * Description of Company.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class Company extends \MultiFlexi\Environmentor implements injector
{
    /**
     * List of all known keys.
     *
     * @return array
     */
    public static function allKeysHandled()
    {
        return ['MULTIFLEXI_COMPANY_ID', 'MULTIFLEXI_COMPANY_CODE', 'MULTIFLEXI_COMPANY_NAME'];
    }

    /**
     * MultiFlexi Related values.
     */
    public function getEnvironment(): \MultiFlexi\ConfigFields
    {
        $envCompany = new \MultiFlexi\ConfigFields(self::name());
        $envCompany->addFields($this->engine->company->getEnvironment());

        $envCompany->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_COMPANY_ID', 'integer'))->setValue((string) $this->engine->company->getMyKey()));
        $envCompany->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_COMPANY_CODE', 'string'))->setValue($this->engine->company->getDataValue('code')));
        $envCompany->addField((new \MultiFlexi\ConfigField('MULTIFLEXI_COMPANY_NAME', 'string'))->setValue($this->engine->company->getRecordName()));

        return $envCompany;
    }

    public static function name(): string
    {
        return _('Company');
    }

    public static function description(): string
    {
        return _('Provide Information about Current company');
    }
}
