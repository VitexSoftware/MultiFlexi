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
 * Job Filter Toolbar Widget.
 *
 * Reusable toolbar with predefined job filters
 */
class JobFilterToolbar extends \Ease\Html\DivTag
{
    /**
     * Current active filter.
     */
    private ?string $activeFilter;

    /**
     * Base URL for filter links.
     */
    private string $baseUrl;

    /**
     * Constructor.
     *
     * @param null|string $activeFilter Currently active filter (success, failed, running, scheduled, today)
     * @param string      $baseUrl      Base URL for filter links (default: joblist.php)
     * @param array       $properties   Additional div properties
     */
    public function __construct(?string $activeFilter = null, string $baseUrl = 'joblist.php', array $properties = [])
    {
        $this->activeFilter = $activeFilter;
        $this->baseUrl = $baseUrl;

        $properties['id'] ??= 'job-filter-buttons';
        parent::__construct(null, $properties);

        self::addToolbarCSS();
        $this->buildButtons();
        self::addRepositioningScript();
    }

    /**
     * Get page title based on active filter.
     *
     * @return string Localized page title
     */
    public static function getPageTitle(?string $filter): string
    {
        switch ($filter) {
            case 'success':
                return _('Successful Jobs');
            case 'failed':
                return _('Failed Jobs');
            case 'running':
                return _('Running Jobs');
            case 'scheduled':
                return _('Scheduled Jobs');
            case 'today':
                return _('Today\'s Jobs');

            default:
                return _('Job history');
        }
    }

    /**
     * Add CSS for responsive layout.
     */
    private static function addToolbarCSS(): void
    {
        WebPage::singleton()->addCSS(<<<'CSS'
#job-filter-buttons {
    display: inline-block;
    margin-right: 1rem;
    vertical-align: middle;
}

#job-filter-buttons .btn-group {
    display: inline-flex;
}

/* Mobile: stack vertically */
@media (max-width: 767px) {
    #job-filter-buttons {
        display: block;
        width: 100%;
        margin-right: 0;
        margin-bottom: 1rem;
    }
    #job-filter-buttons .btn-group {
        display: flex;
        flex-direction: column;
        width: 100%;
    }
    #job-filter-buttons .btn-group a {
        width: 100%;
        border-radius: 0.25rem !important;
        margin-bottom: 0.25rem;
    }
}
CSS);
    }

    /**
     * Build filter buttons.
     */
    private function buildButtons(): void
    {
        $buttonGroup = $this->addItem(new \Ease\Html\DivTag(null, ['class' => 'btn-group btn-group-sm', 'role' => 'group']));

        // All Jobs button
        $allJobsClass = empty($this->activeFilter) ? 'btn btn-primary' : 'btn btn-outline-primary';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl, '🏁 '._('All Jobs'), ['class' => $allJobsClass,'title' => _('Show all jobs'),'id' => 'alljobsbuttonmain']));

        // Success button
        $successClass = ($this->activeFilter === 'success') ? 'btn btn-success' : 'btn btn-outline-success';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl.'?filter=success', '✅ '._('Successful'), ['class' => $successClass,'title' => _('Show successful jobs'),'id' => 'successfuljobsbuttonmain']));
        // Failed button
        $failedClass = ($this->activeFilter === 'failed') ? 'btn btn-danger' : 'btn btn-outline-danger';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl.'?filter=failed', '❌ '._('Failed'), ['class' => $failedClass,'title' => _('Show failed jobs'),'id' => 'failedjobsbuttonmain']));

        // Running button
        $runningClass = ($this->activeFilter === 'running') ? 'btn btn-info' : 'btn btn-outline-info';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl.'?filter=running', '▶️ '._('Running'), ['class' => $runningClass,'title' => _('Show running jobs'),'id' => 'runningjobsbuttonmain']));

        // Scheduled/Waiting button
        $scheduledClass = ($this->activeFilter === 'scheduled') ? 'btn btn-secondary' : 'btn btn-outline-secondary';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl.'?filter=scheduled', '💣 '._('Scheduled'), ['class' => $scheduledClass,'title' => _('Show scheduled jobs'),'id' => 'scheduledjobsbuttonmain']));
        // Today button
        $todayClass = ($this->activeFilter === 'today') ? 'btn btn-warning' : 'btn btn-outline-warning';
        $buttonGroup->addItem(new \Ease\Html\ATag($this->baseUrl.'?filter=today', '📅 '._('Today'), ['class' => $todayClass,'title' => _('Show today\'s jobs'),'id' => 'todayjobsbuttonmain']));
    }

    /**
     * Add JavaScript to reposition buttons next to DataTable controls.
     */
    private static function addRepositioningScript(): void
    {
        WebPage::singleton()->addJavaScript(<<<'EOD'
$(document).ready(function() {
    // Wait for DataTable to be fully initialized
    setTimeout(function() {
        var filterButtons = $('#job-filter-buttons');
        var tableWrapper = $('.dataTables_wrapper');

        if (filterButtons.length && tableWrapper.length) {
            // On desktop: prepend to the first row (where length and filter are)
            if ($(window).width() >= 768) {
                tableWrapper.find('.row:first .col-sm-12:first').prepend(filterButtons);
            }
            // On mobile: keep as is (already before table)
        }

        // Handle window resize
        $(window).on('resize', function() {
            var filterButtons = $('#job-filter-buttons');
            if ($(window).width() >= 768) {
                // Move to DataTable controls area
                if (!tableWrapper.find('#job-filter-buttons').length) {
                    tableWrapper.find('.row:first .col-sm-12:first').prepend(filterButtons);
                }
            } else {
                // Move back before table
                if (tableWrapper.find('#job-filter-buttons').length) {
                    tableWrapper.before(filterButtons);
                }
            }
        });
    }, 100);
});
EOD);
    }
}
