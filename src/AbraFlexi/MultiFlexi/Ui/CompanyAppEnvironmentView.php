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
 * Show Full environment for Application
 *
 * @author vitex
 */
class CompanyAppEnvironmentView extends EnvironmentView
{
    public function __construct(int $companyAppId, $properties = [])
    {
        $appToCompany = new \AbraFlexi\MultiFlexi\AppToCompany($companyAppId);
        parent::__construct($appToCompany->getAppEnvironment(), $properties);
    }
}