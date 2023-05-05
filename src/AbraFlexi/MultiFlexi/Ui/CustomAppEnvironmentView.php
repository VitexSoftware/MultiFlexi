<?php

declare(strict_types=1);
/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */
/**
 * 
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

/**
 * 
 * Show Customized App Environment only
 *
 * @author vitex
 */
class CustomAppEnvironmentView extends EnvironmentView
{

    public function __construct(int $appCompanyID, $properties = [])
    {
        $appToCompany = new \AbraFlexi\MultiFlexi\AppToCompany($appCompanyID);
        $companyId = $appToCompany->getDataValue('company_id');
        $appId = $appToCompany->getDataValue('app_id');
        $customConfig = new \AbraFlexi\MultiFlexi\Configuration();
        $appFields = \AbraFlexi\MultiFlexi\Conffield::getAppConfigs($appId);
        
        $appConfig = array_combine(array_keys($appFields), array_fill(0,count($appFields), new \Ease\TWB4\Badge('warning', 'unset') ) );
        
        foreach ($customConfig->getAppConfig($companyId, $appId) as $cfg) {
            $appConfig[$cfg['name']] = $cfg['value'];
        }
        parent::__construct($appConfig, $properties);
    }
}
