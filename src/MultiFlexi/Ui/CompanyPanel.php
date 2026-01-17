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
 *
 * @no-named-arguments
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

        // Company logo and name - smaller
        $headRow->addColumn(2, new \Ease\Html\ATag('company.php?id='.$cid, [new CompanyLogo($company, ['style' => 'height: 50px']), '&nbsp;', new \Ease\Html\SmallTag($company->getDataValue('code'))], ['class' => 'd-flex align-items-center']));

        // Action buttons - more compact, using btn-sm instead of btn-lg
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companysetup.php?id='.$cid, '🛠️&nbsp;'._('Setup'), 'light btn-sm btn-block', ['title' => _('Setup Company'), 'id' => 'setupcompanybutton']));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companyapps.php?company_id='.$cid, '📌&nbsp;'._('Applications'), 'light btn-sm btn-block', ['title' => _('Manage Applications'), 'id' => 'applicationscompanysetupbutton']));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('activation-wizard.php?company='.$company->getMyKey(), '🧙&nbsp;'._('Wizard'), 'light btn-sm btn-block', ['title' => _('Activation Wizard'), 'id' => 'activationwizardcompanysetupbutton']));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companycreds.php?company_id='.$company->getMyKey(), '🔐&nbsp;'._('Credentials'), 'light btn-sm btn-block', ['title' => _('Manage Credentials'), 'id' => 'managecredentialscompanysetupbutton']));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('joblist.php?company_id='.$company->getMyKey(), '🏁&nbsp;'._('Jobs'), 'light btn-sm btn-block', ['title' => _('Job List'), 'id' => 'joblistcompanysetupbutton']));
        parent::__construct($headRow, 'default', $content, $footer);
    }
}
