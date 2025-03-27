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
 * Description of CompanySelect.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CompanySelect extends \Ease\Html\SelectTag
{
    public function __construct(string $name, ?int $defaultValue, array $properties = [])
    {
        $companer = new \MultiFlexi\Company();

        $companies['0'] = _('Please Select Company');

        foreach ($companer->listingQuery() as $company) {
            $companies[(string) $company['id']] = empty($company['name']) ? (string) ($company['id']) : $company['name'];
        }

        parent::__construct($name, $companies, (string) $defaultValue, $properties);
    }
}
