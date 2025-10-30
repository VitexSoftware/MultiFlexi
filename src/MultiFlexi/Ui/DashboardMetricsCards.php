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
 * Dashboard metrics cards widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2024 Vitex Software
 */
class DashboardMetricsCards extends \Ease\TWB4\Row
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $jobber = new \MultiFlexi\Job();
        $apper = new \MultiFlexi\Application();
        $companyer = new \MultiFlexi\Company();
        $runtempler = new \MultiFlexi\RunTemplate();
        
        // Metriky pro karty
        $totalJobs = $jobber->listingQuery()->count();
        $totalApps = $apper->listingQuery()->where('enabled', 1)->count();
        $totalCompanies = $companyer->listingQuery()->where('enabled', 1)->count();
        $totalRunTemplates = $runtempler->listingQuery()->count();
        
        // Celkem jobů
        $card1 = new \Ease\TWB4\Card(null, ['class' => 'bg-primary text-white']);
        $card1Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card1Body->addItem(new \Ease\Html\H5Tag(_('Total Jobs'), ['class' => 'card-title']));
        $card1Body->addItem(new \Ease\Html\H2Tag($totalJobs, ['class' => 'display-4 mb-3']));
        $card1Body->addItem(new \Ease\Html\ATag('jobs.php', '🔍 '._('View Jobs'), ['class' => 'btn btn-light btn-sm']));
        $card1->addItem($card1Body);
        $this->addColumn(3, $card1);
        
        // Aktivní aplikace
        $card2 = new \Ease\TWB4\Card(null, ['class' => 'bg-info text-white']);
        $card2Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card2Body->addItem(new \Ease\Html\H5Tag(_('Active Applications'), ['class' => 'card-title']));
        $card2Body->addItem(new \Ease\Html\H2Tag($totalApps, ['class' => 'display-4 mb-3']));
        $card2Body->addItem(new \Ease\Html\ATag('apps.php', '📦 '._('View Apps'), ['class' => 'btn btn-light btn-sm']));
        $card2->addItem($card2Body);
        $this->addColumn(3, $card2);
        
        // Aktivní firmy
        $card3 = new \Ease\TWB4\Card(null, ['class' => 'bg-success text-white']);
        $card3Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card3Body->addItem(new \Ease\Html\H5Tag(_('Active Companies'), ['class' => 'card-title']));
        $card3Body->addItem(new \Ease\Html\H2Tag($totalCompanies, ['class' => 'display-4 mb-3']));
        $card3Body->addItem(new \Ease\Html\ATag('companies.php', '🏢 '._('View Companies'), ['class' => 'btn btn-light btn-sm']));
        $card3->addItem($card3Body);
        $this->addColumn(3, $card3);
        
        // RunTemplates
        $card4 = new \Ease\TWB4\Card(null, ['class' => 'bg-warning text-dark']);
        $card4Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card4Body->addItem(new \Ease\Html\H5Tag(_('Run Templates'), ['class' => 'card-title']));
        $card4Body->addItem(new \Ease\Html\H2Tag($totalRunTemplates, ['class' => 'display-4 mb-3']));
        $card4Body->addItem(new \Ease\Html\ATag('runtemplates.php', '⚙️ '._('View Templates'), ['class' => 'btn btn-dark btn-sm']));
        $card4->addItem($card4Body);
        $this->addColumn(3, $card4);
    }
}
