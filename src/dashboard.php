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

require_once './init.php';

$oPage = WebPage::singleton();
$oPage->onlyForLogged();

$oPage->addItem(new PageTop(_('Dashboard')));

// Karty s metrikami
$oPage->container->addItem(new DashboardMetricsCards());

// Druhý řádek - úspěšnost jobů
$oPage->container->addItem(new DashboardStatusCards());

// Grafy
$chartsRow = new \Ease\TWB4\Row();
$chartsRow->addColumn(6, new DashboardJobsByAppChart());
$chartsRow->addColumn(6, new DashboardJobsByCompanyChart());
$oPage->container->addItem($chartsRow);

// Graf 3: Timeline exekucí za posledních 7 dní
$timelineRow = new \Ease\TWB4\Row();
$timelineRow->addColumn(12, new DashboardTimelineChart());
$oPage->container->addItem($timelineRow);

// Graf 4: RunTemplates podle intervalů
$intervalRow = new \Ease\TWB4\Row();
$intervalRow->addColumn(12, new DashboardIntervalChart());
$oPage->container->addItem($intervalRow);

// Tabulka s posledními joby
$recentJobsRow = new \Ease\TWB4\Row();
$recentJobsRow->addColumn(12, new DashboardRecentJobsTable());
$oPage->container->addItem($recentJobsRow);

// CSS pro grafy a karty
$oPage->addCSS(DashboardStyles::getStyles());

$oPage->addItem(new PageBottom());
$oPage->draw();
