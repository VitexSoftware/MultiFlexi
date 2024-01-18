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
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyPanel
 *
 * @author vitex
 */
class CompanyPanel extends \Ease\TWB4\Panel
{
    /**
     *
     * @param \MultiFlexi\Company $company
     * @param mixed               $content
     * @param mixed               $footer
     */
    public function __construct($company, $content = null, $footer = null)
    {
        $cid = $company->getMyKey();
        $headRow = new \Ease\TWB4\Row();
        $headRow->addColumn(2, new \Ease\Html\ATag('company.php?id=' . $cid, [new CompanyLogo($company, ['style' => 'height: 60px']), '&nbsp;', $company->getRecordName()]));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companysetup.php?id=' . $cid, '🛠️&nbsp;' . _('Company'), 'primary btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('companyapps.php?company_id=' . $cid, '📌&nbsp;' . _('Applications'), 'secondary btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('tasks.php?company_id=' . $cid, '🔧&nbsp;' . _('Setup tasks'), 'secondary btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('adhoc.php?company_id=' . $cid, '🚀&nbsp;' . _('Application launcher'), 'secondary btn-lg btn-block'));
        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('periodical.php?company_id=' . $cid, '🔁&nbsp;' . _('Periodical Tasks'), 'secondary btn-lg btn-block'));
        parent::__construct($headRow, 'default', $content, $footer);
    }
}
