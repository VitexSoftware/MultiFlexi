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
     * Company Environment.
     */
    public function getEnvironment(): array
    {
        $companyEnvironment = [];
        $companyEnvironmentRaw = $this->engine->company->getEnvironment();
        $companyEnvironment['MULTIFLEXI_COMPANY_ID']['value'] = $this->engine->company->getMyKey();
        $companyEnvironment['MULTIFLEXI_COMPANY_CODE']['value'] = $this->engine->company->getDataValue('code');
        $companyEnvironment['MULTIFLEXI_COMPANY_NAME']['value'] = $this->engine->company->getRecordName();

        if ($companyEnvironmentRaw) {
            foreach ($companyEnvironmentRaw as $key => $value) {
                $companyEnvironment[$key]['value'] = $value;
            }
        }

        return $this->addMetaData($this->addSelfAsSource($companyEnvironment));
    }

    /**
     * @return string
     */
    public static function name()
    {
        return _('Company');
    }

    /**
     * @return string
     */
    public static function description()
    {
        return _('Provide Information about Current company');
    }
}
