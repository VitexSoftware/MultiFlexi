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

namespace MultiFlexi\Tests\Ui;

use PHPUnit\Framework\TestCase;

/**
 * Dashboard Widgets Test Suite.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DashboardWidgetsTest extends TestCase
{
    /**
     * Test that DashboardMetricsCards can be instantiated.
     */
    public function testDashboardMetricsCardsInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardMetricsCards();
        // $this->assertInstanceOf(\Ease\TWB4\Row::class, $widget);
    }

    /**
     * Test that DashboardStatusCards can be instantiated.
     */
    public function testDashboardStatusCardsInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardStatusCards();
        // $this->assertInstanceOf(\Ease\TWB4\Row::class, $widget);
    }

    /**
     * Test that DashboardJobsByAppChart can be instantiated.
     */
    public function testDashboardJobsByAppChartInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardJobsByAppChart();
        // $this->assertInstanceOf(\Ease\Html\DivTag::class, $widget);
    }

    /**
     * Test that DashboardJobsByCompanyChart can be instantiated.
     */
    public function testDashboardJobsByCompanyChartInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardJobsByCompanyChart();
        // $this->assertInstanceOf(\Ease\Html\DivTag::class, $widget);
    }

    /**
     * Test that DashboardTimelineChart can be instantiated.
     */
    public function testDashboardTimelineChartInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardTimelineChart();
        // $this->assertInstanceOf(\Ease\Html\DivTag::class, $widget);
    }

    /**
     * Test that DashboardIntervalChart can be instantiated.
     */
    public function testDashboardIntervalChartInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardIntervalChart();
        // $this->assertInstanceOf(\Ease\Html\DivTag::class, $widget);
    }

    /**
     * Test that DashboardRecentJobsTable can be instantiated.
     */
    public function testDashboardRecentJobsTableInstantiation(): void
    {
        $this->markTestSkipped('Requires database connection and environment setup');
        
        // This would require database setup
        // $widget = new \MultiFlexi\Ui\DashboardRecentJobsTable();
        // $this->assertInstanceOf(\Ease\Html\DivTag::class, $widget);
    }

    /**
     * Test DashboardStyles returns valid CSS.
     */
    public function testDashboardStylesReturnsValidCss(): void
    {
        $styles = \MultiFlexi\Ui\DashboardStyles::getStyles();
        
        $this->assertIsString($styles);
        $this->assertStringContainsString('.chart-container', $styles);
        $this->assertStringContainsString('.card', $styles);
        $this->assertStringContainsString('.card-body', $styles);
        $this->assertStringContainsString('.card-title', $styles);
        $this->assertStringContainsString('.display-4', $styles);
    }

    /**
     * Test DashboardStyles CSS contains required properties.
     */
    public function testDashboardStylesContainsRequiredProperties(): void
    {
        $styles = \MultiFlexi\Ui\DashboardStyles::getStyles();
        
        $this->assertStringContainsString('margin:', $styles);
        $this->assertStringContainsString('padding:', $styles);
        $this->assertStringContainsString('text-align:', $styles);
        $this->assertStringContainsString('font-size:', $styles);
    }
}
