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
     * @param array<string, string> $properties
     */
    public function __construct(int $appCompanyID, $properties = [])
    {
        $appConfig = [];
        $appToCompany = new \MultiFlexi\RunTemplate($appCompanyID);
        $companyId = $appToCompany->getDataValue('company_id');
        $appId = $appToCompany->getDataValue('app_id');
        $customConfig = new \MultiFlexi\Configuration();
        $appFields = \MultiFlexi\Conffield::getAppConfigs(new \MultiFlexi\Application($appId));
        $appFields->addFields($appToCompany->getAppEnvironment());
        $appFields->arrayToValues($customConfig->getAppConfig($companyId, $appId));

        parent::__construct($appFields, $properties);
    }
}
