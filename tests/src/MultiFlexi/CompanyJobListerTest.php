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

namespace MultiFlexi;

use PHPUnit\Framework\TestCase;

/**
 * Test case for CompanyJobLister filtering functionality.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class CompanyJobListerTest extends TestCase
{
    private CompanyJobLister $lister;

    protected function setUp(): void
    {
        $this->lister = new CompanyJobLister();
    }

    public function testApplyFilterSuccess(): void
    {
        $this->lister->applyFilter('success');

        $this->assertEquals('success', $this->lister->filterType);
        $this->assertArrayHasKey('_jobfilter', $this->lister->filter);
        $this->assertEquals('success', $this->lister->filter['_jobfilter']);
    }

    public function testApplyFilterFailed(): void
    {
        $this->lister->applyFilter('failed');

        $this->assertEquals('failed', $this->lister->filterType);
        $this->assertArrayHasKey('_jobfilter', $this->lister->filter);
        $this->assertEquals('failed', $this->lister->filter['_jobfilter']);
    }

    public function testApplyFilterRunning(): void
    {
        $this->lister->applyFilter('running');

        $this->assertEquals('running', $this->lister->filterType);
        $this->assertArrayHasKey('_jobfilter', $this->lister->filter);
        $this->assertEquals('running', $this->lister->filter['_jobfilter']);
    }

    public function testApplyFilterToday(): void
    {
        $this->lister->applyFilter('today');

        $this->assertEquals('today', $this->lister->filterType);
        $this->assertArrayHasKey('_jobfilter', $this->lister->filter);
        $this->assertEquals('today', $this->lister->filter['_jobfilter']);
    }

    public function testConstructorRestoresFilterFromArray(): void
    {
        $filter = ['_jobfilter' => 'failed'];
        $lister = new CompanyJobLister(null, $filter);

        $this->assertEquals('failed', $lister->filterType);
    }

    public function testGetAllForDataTableExcludesJobfilterParameter(): void
    {
        $conditions = [
            '_jobfilter' => 'failed',
            'draw' => '1',
            'start' => '0',
            'length' => '10',
        ];

        // This should not throw an exception about unknown column '_jobfilter'
        // The method should extract and remove _jobfilter before processing
        $this->lister->getAllForDataTable($conditions);

        // Verify filter was set from conditions
        $this->assertEquals('failed', $this->lister->filterType);
    }

    public function testFilterTypePersistenceAcrossInstances(): void
    {
        // Simulate first request
        $lister1 = new CompanyJobLister();
        $lister1->applyFilter('success');

        // Get filter array that would be passed to AJAX
        $filter = $lister1->filter;

        // Simulate AJAX request creating new instance
        $lister2 = new CompanyJobLister(null, $filter);

        // Filter should be restored
        $this->assertEquals('success', $lister2->filterType);
    }
}
