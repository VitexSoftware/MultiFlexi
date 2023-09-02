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

namespace MultiFlexi\Ui;

/**
 * Show Full environment for Application
 *
 * @author vitex
 */
class CompanyAppEnvironmentView extends EnvironmentView
{
    public function __construct(int $runTemplateId, $properties = [])
    {
        $appToCompany = new \AbraFlexi\MultiFlexi\RunTemplate($runTemplateId);
        parent::__construct($appToCompany->getAppEnvironment(), $properties);
    }
}
