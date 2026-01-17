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
 *
 * @no-named-arguments
 */
class ApplicationPanel extends Panel
{
    public Row $headRow;
    private Application $application;

    /**
     * Application Panel.
     *
     * @param null|mixed $content
     * @param null|mixed $footer
     */
    public function __construct(Application $application, $content = null, $footer = null)
    {
        $this->application = $application;
        $this->headRow = new Row();
        
        $logoCol = $this->headRow->addColumn(2, new \Ease\Html\ATag('app.php?id='.$this->application->getMyKey(), [new AppLogo($application, ['style' => 'height: 80px', 'class' => 'img-thumbnail shadow-sm'])]));
        $logoCol->addTagClass('text-center my-auto');

        $titleCol = $this->headRow->addColumn(4, [
            new \Ease\Html\H2Tag($application->getRecordName(), ['class' => 'mb-0']),
            new \Ease\Html\SmallTag($application->getDataValue('uuid'), ['class' => 'text-muted d-block small']),
        ]);
        $titleCol->addTagClass('my-auto');

        $ca = new \MultiFlexi\CompanyApp(null);
        $usedIncompanies = $ca->listingQuery()->select(['companyapp.company_id', 'company.name', 'company.slug', 'company.logo'], true)->leftJoin('company ON company.id = companyapp.company_id')->where('app_id', $this->application->getMyKey())->fetchAll('company_id');

        if ($usedIncompanies) {
            $usedByDiv = new \Ease\Html\DivTag(null, ['class' => 'p-2 bg-light rounded shadow-sm border']);
            $usedByDiv->addItem(new \Ease\Html\SmallTag(_('Used by').': ', ['class' => 'font-weight-bold mb-1 d-block text-uppercase small text-secondary']));

            // Create compact table instead of cards
            $usedByTable = new \Ease\TWB4\Table(null, ['class' => 'table table-sm table-hover mb-0', 'style' => 'font-size: 0.85rem;']);
            // $usedByTable->addRowHeaderColumns([_('Company'), _('RunTemplates')]);

            foreach ($usedIncompanies as $companyInfo) {
                $companyInfo['id'] = $companyInfo['company_id'];
                $kumpan = new \MultiFlexi\Company($companyInfo, ['autoload' => false]);
                $calb = new CompanyAppLink($kumpan, $application);
                $crls = new \MultiFlexi\Ui\CompanyRuntemplatesLinks($kumpan, $application, [], ['class' => 'btn btn-outline-secondary btn-sm p-0 px-1', 'style' => 'font-size: 0.7rem;']);

                $usedByTable->addRowColumns([
                    [$calb, ' ', new \Ease\Html\SmallTag('('.$crls->count().')', ['class' => 'text-muted'])],
                    $crls,
                ]);
            }

            $usedByDiv->addItem($usedByTable);
            $this->headRow->addColumn(4, $usedByDiv);
        } else {
            $this->headRow->addColumn(4, '');
        }

        if ($application->getMyKey()) {
            $actionCol = $this->headRow->addColumn(2, new LinkButton('?id='.$application->getMyKey().'&action=delete', '🪦&nbsp;'._('Remove'), 'outline-danger btn-sm shadow-sm'));
            $actionCol->addTagClass('text-right my-auto');
        }

        parent::__construct($this->headRow, 'default', $content, $footer);
    }

    #[\Override]
    public function finalize(): void
    {
        $this->footer->addItem(new LinkButton('joblist.php?app_id='.$this->application->getMyKey(), '🧑‍💻&nbsp;'._('App Jobs'), 'secondary btn-lg', ['title' => _('View application jobs'), 'id' => 'appjobsbutton']));
        parent::finalize();
    }
}
