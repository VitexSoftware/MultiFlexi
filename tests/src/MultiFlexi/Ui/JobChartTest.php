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

namespace Test\MultiFlexi\Ui;

use MultiFlexi\Ui\JobChart;

/**
 * Tests for JobChart widget.
 *
 * JobChart renders a stacked bar chart of job execution results for the last
 * 30 days. Every day in the window is always shown — days with no jobs produce
 * a zero-height column so gaps in activity are visible.
 * The legend is rendered as HTML beside the SVG (not overlaid on it).
 *
 * @covers \MultiFlexi\Ui\JobChart
 *
 * @no-named-arguments
 */
class JobChartTest extends \PHPUnit\Framework\TestCase
{
    /**
     * JobChart must extend \Ease\Html\DivTag so it can be embedded in pages.
     */
    public function testJobChartIsSubclassOfDivTag(): void
    {
        $this->assertTrue(
            is_subclass_of(JobChart::class, \Ease\Html\DivTag::class),
            'JobChart must extend \Ease\Html\DivTag',
        );
    }

    /**
     * The class must declare the getJobs() method used by subclasses.
     */
    public function testJobChartHasGetJobsMethod(): void
    {
        $this->assertTrue(
            method_exists(JobChart::class, 'getJobs'),
            'JobChart must have a getJobs() method',
        );
    }

    /**
     * getJobs() must return a FluentPDO Select query filtered to the last 30 days.
     *
     * The WHERE clause must include `begin >= '\''YYYY-MM-DD'\''` so only jobs
     * from the 30-day window are loaded, keeping the query efficient.
     *
     * @covers \MultiFlexi\Ui\JobChart::getJobs
     */
    public function testGetJobsFiltersToLastThirtyDays(): void
    {
        $this->markTestSkipped('Requires database connection');
        // When implemented:
        // $engine = new \MultiFlexi\Job();
        // $chart  = new JobChart($engine);
        // $query  = $chart->getJobs();
        // $sql    = (string) $query;
        // $since  = (new \DateTimeImmutable('today'))->modify('-29 days')->format('Y-m-d');
        // $this->assertStringContainsString($since, $sql);
    }

    /**
     * The rendered HTML must always contain exactly 30 bar columns — one per
     * day — even when some days have no jobs (zero-height columns).
     */
    public function testChartAlwaysRendersThirtyDayColumns(): void
    {
        $this->markTestSkipped('Requires database connection');
        // When implemented:
        // $engine = new \MultiFlexi\Job();
        // $chart  = new JobChart($engine);
        // $html   = (string) $chart;
        // Expect 30 SVG <rect> groups (one per day)
        // $this->assertSame(30, substr_count($html, 'class="series'));
    }

    /**
     * The legend must be rendered as an HTML element beside the chart, NOT
     * as an SVG overlay. This prevents it from obscuring the first columns.
     *
     * Expected items: waiting, fail, success, exception.
     */
    public function testLegendIsRenderedAsExternalHtml(): void
    {
        $this->markTestSkipped('Requires database connection');
        // When implemented:
        // $engine = new \MultiFlexi\Job();
        // $chart  = new JobChart($engine);
        // $html   = (string) $chart;
        // Legend wrapper must exist outside the <svg> element
        // $this->assertStringContainsString('d-flex align-items-stretch', $html);
        // $this->assertStringContainsString('border-start', $html);
        // Each status must have a coloured swatch
        // $this->assertStringContainsString('#B3E5FC', $html); // waiting
        // $this->assertStringContainsString('#FFCDD2', $html); // fail
        // $this->assertStringContainsString('#C8E6C9', $html); // success
        // $this->assertStringContainsString('#E0E0E0', $html); // exception
    }
}
