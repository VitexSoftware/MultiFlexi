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
 * Dashboard status cards widget.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright 2023-2024 Vitex Software
 */
class DashboardStatusCards extends \Ease\TWB4\Row
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();
        
        $jobber = new \MultiFlexi\Job();
        
        $totalJobs = $jobber->listingQuery()->count();
        $successJobs = $jobber->listingQuery()->where('exitcode', 0)->where('exitcode IS NOT NULL')->count();
        $failedJobs = $jobber->listingQuery()->where('exitcode <> 0')->where('exitcode IS NOT NULL')->count();
        $runningJobs = $jobber->listingQuery()->where('begin IS NOT NULL')->where('end IS NULL')->count();
        
        // Dnešní joby
        $todayCondition = $jobber->todaysCond('begin');
        $todayJobs = $jobber->listingQuery()->where($todayCondition)->count();
        
        // Úspěšné joby
        $successRate = $totalJobs > 0 ? round(($successJobs / $totalJobs) * 100) : 0;
        $card5 = new \Ease\TWB4\Card(null, ['class' => 'bg-success text-white']);
        $card5Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card5Body->addItem(new \Ease\Html\H5Tag(_('Successful Jobs'), ['class' => 'card-title']));
        $card5Body->addItem(new \Ease\Html\H2Tag($successJobs, ['class' => 'display-4']));
        $card5Body->addItem(new \Ease\Html\SmallTag(sprintf(_('%d%% success rate'), $successRate), ['class' => 'd-block mt-2']));
        $card5Body->addItem(new \Ease\Html\ATag('joblist.php?filter=success', '✓ '._('View All'), ['class' => 'btn btn-light btn-sm mt-2']));
        $card5->addItem($card5Body);
        $this->addColumn(3, $card5);
        
        // Neúspěšné joby
        $failureRate = $totalJobs > 0 ? round(($failedJobs / $totalJobs) * 100) : 0;
        $card6 = new \Ease\TWB4\Card(null, ['class' => 'bg-danger text-white']);
        $card6Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card6Body->addItem(new \Ease\Html\H5Tag(_('Failed Jobs'), ['class' => 'card-title']));
        $card6Body->addItem(new \Ease\Html\H2Tag($failedJobs, ['class' => 'display-4']));
        $card6Body->addItem(new \Ease\Html\SmallTag(sprintf(_('%d%% failure rate'), $failureRate), ['class' => 'd-block mt-2']));
        $card6Body->addItem(new \Ease\Html\ATag('joblist.php?filter=failed', '✗ '._('View All'), ['class' => 'btn btn-light btn-sm mt-2']));
        $card6->addItem($card6Body);
        $this->addColumn(3, $card6);
        
        // Běžící joby
        $card7 = new \Ease\TWB4\Card(null, ['class' => 'bg-primary text-white']);
        $card7Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card7Body->addItem(new \Ease\Html\H5Tag(_('Running Jobs'), ['class' => 'card-title']));
        $card7Body->addItem(new \Ease\Html\H2Tag($runningJobs, ['class' => 'display-4']));
        $card7Body->addItem(new \Ease\Html\SmallTag(_('Currently executing'), ['class' => 'd-block mt-2']));
        $card7Body->addItem(new \Ease\Html\ATag('joblist.php?filter=running', '▶️ '._('View All'), ['class' => 'btn btn-light btn-sm mt-2']));
        $card7->addItem($card7Body);
        $this->addColumn(3, $card7);
        
        // Dnešní joby
        $card8 = new \Ease\TWB4\Card(null, ['class' => 'bg-info text-white']);
        $card8Body = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center']);
        $card8Body->addItem(new \Ease\Html\H5Tag(_('Today\'s Jobs'), ['class' => 'card-title']));
        $card8Body->addItem(new \Ease\Html\H2Tag($todayJobs, ['class' => 'display-4']));
        $card8Body->addItem(new \Ease\Html\SmallTag((new \DateTime())->format('Y-m-d'), ['class' => 'd-block mt-2']));
        $card8Body->addItem(new \Ease\Html\ATag('joblist.php?filter=today', '📅 '._('View All'), ['class' => 'btn btn-light btn-sm mt-2']));
        $card8->addItem($card8Body);
        $this->addColumn(3, $card8);
    }
}
