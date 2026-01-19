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
 * Description of CompanyLogo.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CompanyLogo extends \Ease\Html\ImgTag
{
    /**
     * Emebed Company logo into page.
     *
     * @param \MultiFlexi\Company   $company       Object
     * @param array<string, string> $tagProperties Additional tag properties
     */
    public function __construct(\MultiFlexi\Company $company, array $tagProperties = [])
    {
        parent::__construct(empty($company->getDataValue('logo')) ? 'images/company.svg' :  $company->getDataValue('logo') , $company->getDataValue('name') ?? _('unamed company').' #'.$company->getMyKey() , $tagProperties);
    }
}
