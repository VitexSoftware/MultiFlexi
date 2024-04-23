<?php

declare(strict_types=1);

/**
 * Multi Flexi - CompanyLink Button
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyLinkButton
 *
 * @author vitex
 */
class CompanyAppLinkButton extends \Ease\TWB4\LinkButton
{
    public function __construct(\MultiFlexi\Company $company, $app_id , $properties = [])
    {
        parent::__construct('companyapp.php?company_id=' . $company->getMyKey().'&app_id='.$app_id, [ new CompanyLogo($company, ['style' => 'height: 100%']),'&nbsp;', $company->getDataValue('code') ? $company->getDataValue('code') : $company->getRecordName()], 'inverse', $properties);
    }
}
