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

        $logoCol = $headRow->addColumn(2, new \Ease\Html\ATag('company.php?id='.$cid, [new CompanyLogo($company, ['style' => 'height: 60px', 'class' => 'img-thumbnail shadow-sm'])]));
        $logoCol->addTagClass('text-center my-auto');

        $titleCol = $headRow->addColumn(4, [
            new \Ease\Html\H2Tag($company->getRecordName() ?: $company->getDataValue('code'), ['class' => 'mb-0']),
            new \Ease\Html\SmallTag($company->getDataValue('code'), ['class' => 'text-muted d-block small']),
        ]);
        $titleCol->addTagClass('my-auto');

        $actionsRow = new \Ease\TWB4\Row();
        $actionsRow->addColumn(4, new \Ease\TWB4\LinkButton('companysetup.php?id='.$cid, '🛠️&nbsp;'._('Setup'), 'outline-secondary btn-sm btn-block shadow-sm mb-1', ['title' => _('Setup Company')]));
        $actionsRow->addColumn(4, new \Ease\TWB4\LinkButton('companyapps.php?company_id='.$cid, '📌&nbsp;'._('Applications'), 'outline-secondary btn-sm btn-block shadow-sm mb-1', ['title' => _('Manage Applications')]));
        $actionsRow->addColumn(4, new \Ease\TWB4\LinkButton('activation-wizard.php?company='.$cid, '🧙&nbsp;'._('Wizard'), 'outline-primary btn-sm btn-block shadow-sm mb-1', ['title' => _('Activation Wizard')]));
        $actionsRow->addColumn(4, new \Ease\TWB4\LinkButton('companycreds.php?company_id='.$cid, '🔐&nbsp;'._('Credentials'), 'outline-secondary btn-sm btn-block shadow-sm', ['title' => _('Manage Credentials')]));
        $actionsRow->addColumn(4, new \Ease\TWB4\LinkButton('joblist.php?company_id='.$cid, '🏁&nbsp;'._('Jobs'), 'outline-info btn-sm btn-block shadow-sm', ['title' => _('Job List')]));

        $headRow->addColumn(6, $actionsRow)->addTagClass('my-auto');

        parent::__construct($headRow, 'default', $content, $footer);
    }
}
