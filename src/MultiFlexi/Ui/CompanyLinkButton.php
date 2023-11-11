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
 * Description of CompanyLinkButton
 *
 * @author vitex
 */
class CompanyLinkButton extends \Ease\TWB4\LinkButton
{
    public function __construct(\MultiFlexi\Company $company, $properties = [])
    {
        parent::__construct('company.php?id=' . $company->getMyKey(), [ new CompanyLogo($company),'&nbsp;', $company->getRecordName() ], 'inverse', $properties);
    }
}
