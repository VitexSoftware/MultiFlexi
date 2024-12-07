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
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyPanel.
 *
 * @author vitex
 */
class CompanyPanel extends \Ease\TWB4\Panel
{
    /**
     * @param \MultiFlexi\Company $company
     * @param mixed               $content
     * @param mixed               $footer
     */
    public function __construct($company, $content = null, $footer = null)
    {
        $cid = $company->getMyKey();
        $headRow = new \Ease\TWB4\Row();
        $headRow->addColumn(2, new \Ease\Html\ATag('company.php?id='.$cid, [new CompanyLogo($company, ['style' => 'height: 60px']), '&nbsp;', $company->getDataValue('code')]));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companiesetup.php?id='.$cid, '🛠️&nbsp;'._('Setup'), 'light btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companyapps.php?company_id='.$cid, '📌&nbsp;'._('Applications'), 'light btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('periodical.php?company_id='.$cid, '🔁&nbsp;'._('Periodical Tasks'), 'light btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('wizard.php?company='.$company->getMyKey(), '🧙🏽‍♂️&nbsp'._('Launch wizard'), 'light btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companycreds.php?company_id='.$company->getMyKey(), '🔐&nbsp'._('Credentials'), 'light btn-lg btn-block'));

        parent::__construct($headRow, 'default', $content, $footer);
    }
}
