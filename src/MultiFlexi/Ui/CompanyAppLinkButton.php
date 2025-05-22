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

namespace MultiFlexi\Ui;

/**
 * Description of CompanyLinkButton.
 *
 * @author vitex
 */
class CompanyAppLinkButton extends \Ease\TWB5\LinkButton
{
    public function __construct(\MultiFlexi\Company $company, \MultiFlexi\Application $app, $properties = [])
    {
        parent::__construct('companyapp.php?company_id='.$company->getMyKey().'&app_id='.$app->getMyKey(), [new CompanyLogo($company, ['style' => 'height: 100%']), '&nbsp;', $company->getDataValue('code') ?: $company->getRecordName()], 'inverse', $properties);
    }
}
