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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Show Customized App Environment only.
 *
 * @author vitex
 */
class CustomAppEnvironmentView extends EnvironmentView
{
    /**
     * @param array $properties
     */
    public function __construct(int $appCompanyID, $properties = [])
    {
        $appConfig = [];
        $appToCompany = new \MultiFlexi\RunTemplate($appCompanyID);
        $companyId = $appToCompany->getDataValue('company_id');
        $appId = $appToCompany->getDataValue('app_id');
        $customConfig = new \MultiFlexi\Configuration();
        $appFields = \MultiFlexi\Conffield::getAppConfigs($appId);

        $envValues = array_merge($appToCompany->getAppEnvironment(), $customConfig->getAppConfig($companyId, $appId));

        //        $appConfig = array_combine(array_keys($appFields), array_fill(0, count($appFields), new \Ease\TWB4\Badge('warning', 'unset')));

        foreach ($appFields as $envName => $envProperties) {
            if (\array_key_exists($envName, $envValues)) {
                $appConfig[$envName]['value'] = $envValues[$envName];
            } else {
                $appConfig[$envName]['value'] = new \Ease\TWB4\Badge('warning', _('unset'));
            }
        }

        parent::__construct($appConfig, $properties);
    }
}
