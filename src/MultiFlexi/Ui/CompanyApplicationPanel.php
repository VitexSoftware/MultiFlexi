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

use Ease\TWB4\LinkButton;
use Ease\TWB4\Panel;
use Ease\TWB4\Row;
use MultiFlexi\Application;

/**
 * Description of ApplicationPanel.
 *
 * @author vitex
 */
class CompanyApplicationPanel extends Panel
{

    public Row $headRow;
    private Application $application;

    /**
     * Application Panel.
     *
     * @param null|mixed $content
     * @param null|mixed $footer
     */
    public function __construct(\MultiFlexi\CompanyApp $companyApp, $content = null, $footer = null)
    {
        $company = $companyApp->getCompany();
        $this->application = $companyApp->getApplication();
        $this->headRow = new Row();
        $this->headRow->addColumn(4, new \Ease\Html\ATag('app.php?id=' . $this->application->getMyKey(), [new AppLogo($this->application, ['style' => 'height: 120px']), '&nbsp;', $this->application->getRecordName()]));

        $usedByCompany = new \Ease\Html\DivTag('', ['class' => 'card-group']);

        $crls = new \MultiFlexi\Ui\CompanyRuntemplatesLinks($company, $this->application, [], ['class' => 'btn btn-outline-secondary btn-sm']);

        $usedByCompany->addItem(new \Ease\TWB4\Card([new \Ease\Html\DivTag([new \Ease\Html\H5Tag([_('Runtemplates') . ': ' , ' <small>' . $crls->count() . '</small>'], ['class' => 'card-title']), $crls], ['class' => 'card-body'])], ['style' => 'width: 6rem;']));

        $this->headRow->addColumn(6, $usedByCompany);

        //        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('tasks.php?application_id=' . $cid, '🔧&nbsp;' . _('Setup tasks'), 'secondary btn-lg btn-block'));
        //        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('adhoc.php?application_id=' . $cid, '🚀&nbsp;' . _('Application launcher'), 'secondary btn-lg btn-block'));
        //        $headRow->addColumn(2, new \Ease\TWB4\LinkButton('periodical.php?application_id=' . $cid, '🔁&nbsp;' . _('Periodical Tasks'), 'secondary btn-lg btn-block'));
        parent::__construct($this->headRow, 'default', $content, $footer);
    }

    #[\Override]
    public function finalize(): void
    {
        $this->footer->addItem(new LinkButton('joblist.php?app_id=' . $this->application->getMyKey(), '🧑‍💻&nbsp;' . _('App Jobs'), 'secondary btn-lg'));
        parent::finalize();
    }
}
