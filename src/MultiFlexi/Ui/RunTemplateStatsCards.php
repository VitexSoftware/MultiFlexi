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
 * RunTemplate Statistics Cards Component.
 *
 * Displays key metrics and statistics for a RunTemplate
 *
 * @author vitex
 */
class RunTemplateStatsCards extends \Ease\TWB4\Row
{
    private \MultiFlexi\RunTemplate $runtemplate;
    private array $stats;

    /**
     * Constructor.
     *
     * @param \MultiFlexi\RunTemplate $runtemplate RunTemplate instance
     */
    public function __construct(\MultiFlexi\RunTemplate $runtemplate)
    {
        $this->runtemplate = $runtemplate;
        $this->stats = $this->calculateStats();

        parent::__construct();


        // Create a single row to container everything
        $mainRow = new \Ease\TWB4\Row();

        // 1) Statistics Cards - Left Column (1/12) - Extremely Vertical compact
        $cardsCol = $mainRow->addColumn(1, new \Ease\Html\DivTag(null, ['class' => 'd-flex flex-column', 'style' => 'gap: 2px;']));

        $statCardStyle = ['style' => 'width: 100%; margin: 0; min-width: 60px;'];
        $cardsCol->addItem(new \Ease\Html\DivTag(self::createCompactStatCard('📊', (string) $this->stats['total_jobs'], _('Total'), 'primary'), $statCardStyle));
        $cardsCol->addItem(new \Ease\Html\DivTag(self::createCompactStatCard('✅', $this->stats['success_rate'].'%', _('Success'), $this->stats['success_rate'] >= 80 ? 'success' : ($this->stats['success_rate'] >= 50 ? 'warning' : 'danger')), $statCardStyle));
        $cardsCol->addItem(new \Ease\Html\DivTag(self::createCompactStatCard('❌', (string) $this->stats['failed_jobs'], _('Failed'), 'danger'), $statCardStyle));
        $cardsCol->addItem(new \Ease\Html\DivTag(self::createCompactStatCard('🔄', (string) $this->stats['running_jobs'], _('Running'), 'info'), $statCardStyle));

        // 2) Metadata Column (Timings & Lifecycle) - Middle Column (5/12)
        $metaCol = $mainRow->addColumn(5, new \Ease\Html\DivTag(null, ['class' => 'pl-2']));

        // Timings Group
        $metaCol->addItem(new \Ease\Html\SmallTag(new \Ease\Html\StrongTag('TIMINGS:'), ['class' => 'text-muted d-block mb-1 font-weight-bold small']));
        $timingsWrap = new \Ease\Html\DivTag(null, ['class' => 'd-flex flex-wrap']);

        if ($this->stats['last_run']) {
            $lastRunDate = new \DateTime($this->stats['last_run']);
            $timingsWrap->addItem(self::createInfoChip('⏱️', _('Last Run'), $lastRunDate->format('H:i'), 'info'));
        }

        if ($this->runtemplate->getDataValue('last_schedule')) {
            $lastScheduleDate = new \DateTime((string) $this->runtemplate->getDataValue('last_schedule'));
            $timingsWrap->addItem(self::createInfoChip('📅', _('Last'), $lastScheduleDate->format('H:i'), 'primary'));
        }

        if ($this->runtemplate->getDataValue('next_schedule')) {
            $nextScheduleDate = new \DateTime((string) $this->runtemplate->getDataValue('next_schedule'));
            $timingsWrap->addItem(self::createInfoChip('⏰', _('Next'), $nextScheduleDate->format('Y-m-d H:i'), 'primary'));
        }

        $metaCol->addItem($timingsWrap);

        $metaCol->addItem(new \Ease\Html\DivTag(null, ['class' => 'mt-2']));

        // Lifecycle Group
        $metaCol->addItem(new \Ease\Html\SmallTag(new \Ease\Html\StrongTag('LIFECYCLE:'), ['class' => 'text-muted d-block mb-1 font-weight-bold small']));
        $lifecycleWrap = new \Ease\Html\DivTag(null, ['class' => 'd-flex flex-wrap']);
        $createdRaw = (string) $this->runtemplate->getDataValue($this->runtemplate->createColumn);
        $updatedRaw = (string) $this->runtemplate->getDataValue($this->runtemplate->lastModifiedColumn);

        if ($createdRaw) {
            $cd = new \DateTime($createdRaw);
            $lifecycleWrap->addItem(self::createInfoChip('🌱', _('Created'), $cd->format('Y-m-d').' ('.self::formatAge($cd).')', 'danger'));
        }

        if ($updatedRaw) {
            $ud = new \DateTime($updatedRaw);
            $lifecycleWrap->addItem(self::createInfoChip('💾', _('Updated'), $ud->format('Y-m-d').' ('.self::formatAge($ud).')', 'danger'));
        }

        $metaCol->addItem($lifecycleWrap);

        // 3) Execution Details Block - Right Column (6/12)
        $execCol = $mainRow->addColumn(6, new \Ease\Html\DivTag(null, ['class' => 'p-2 bg-light rounded border shadow-sm']));
        $execCol->addItem(new \Ease\Html\SmallTag(new \Ease\Html\StrongTag('EXECUTION:'), ['class' => 'text-muted d-block mb-2 font-weight-bold small']));

        $execChips = new \Ease\Html\DivTag(null, ['class' => 'd-flex flex-wrap']);
        $active = (bool) $this->runtemplate->getDataValue('active');
        $execChips->addItem(self::createInfoChip($active ? '🟢' : '🔴', _('Status'), $active ? _('Enabled') : _('Disabled'), $active ? 'success' : 'danger'));

        if ($interval = (string) $this->runtemplate->getDataValue('interv')) {
            $execChips->addItem(self::createInfoChip('⏳', _('Interval'), self::formatInterval($interval), 'info'));
        }

        if ($cron = (string) $this->runtemplate->getDataValue('cron')) {
            $execChips->addItem(self::createInfoChip('🧭', 'Cron', $cron, 'dark'));
        }

        $delay = (int) ($this->runtemplate->getDataValue('delay') ?? 0);
        $execChips->addItem(self::createInfoChip('⏱', _('Delay'), self::formatDuration($delay), 'danger'));

        if ($executor = (string) $this->runtemplate->getDataValue('executor')) {
            $execChips->addItem(self::createInfoChip('⚙️', _('Executor'), $executor, 'dark'));
        }

        $execCol->addItem($execChips);

        // Visualization Row: Job Graph Widget
        $vizRow = new \Ease\TWB4\Row();
        $vizRow->addTagClass('mt-3');
        $jobGraphWidget = new \Ease\Html\DivTag([
            new \Ease\Html\H5Tag(_('Recent Jobs Visualization'), ['class' => 'mb-2 font-weight-bold text-muted text-uppercase small']),
            new \MultiFlexi\Ui\JobGraphWidget($this->runtemplate, 20, 10),
        ], ['class' => 'col-12 p-2 border-top']);
        $vizRow->addItem($jobGraphWidget);

        // Chart Row: Full-width Job Chart
        $chartRow = new \Ease\TWB4\Row();
        $chartRow->addTagClass('mt-2');
        $chartCol = $chartRow->addColumn(12, new \MultiFlexi\Ui\RunTemplateJobsLastMonthChart($this->runtemplate, ['style' => 'width: 100%;']));

        $this->addItem($mainRow);
        $this->addItem($vizRow);
        $this->addItem($chartRow);
    }

    /**
     * Create a small styled chip for metadata display.
     */
    private static function createInfoChip(string $icon, string $label, string $value, string $context): \Ease\Html\SpanTag
    {
        $chip = new \Ease\Html\SpanTag(null, ['class' => 'badge badge-'.$context.' mr-2 mb-1 p-1 px-2 border', 'style' => 'font-weight: 500; font-size: 0.75rem; vertical-align: middle;']);
        $chip->addItem(new \Ease\Html\SpanTag($icon, ['class' => 'mr-1']));
        $chip->addItem(new \Ease\Html\SmallTag($label.': ', ['style' => 'opacity: 0.8; font-weight: normal;']));
        $chip->addItem($value);

        return $chip;
    }

    /**
     * Format interval code to human friendly text.
     */
    private static function formatInterval(string $code): string
    {
        $map = [
            'n' => _('Manual'),
            'h' => _('Hourly'),
            'd' => _('Daily'),
            'w' => _('Weekly'),
            'm' => _('Monthly'),
            'c' => _('Custom (cron)'),
        ];

        return $map[$code] ?? $code;
    }

    /**
     * Format seconds to HH:MM:SS.
     */
    private static function formatDuration(int $seconds): string
    {
        if ($seconds < 0) {
            $seconds = 0;
        }

        return gmdate('H:i:s', $seconds);
    }

    /**
     * Format relative age like "7d 3h 2m 1s".
     */
    private static function formatAge(\DateTime $date): string
    {
        $now = new \DateTime('now');
        $diff = $now->diff($date);
        $parts = [];

        if ($diff->d) {
            $parts[] = $diff->d.'d';
        }

        if ($diff->h) {
            $parts[] = $diff->h.'h';
        }

        if ($diff->i) {
            $parts[] = $diff->i.'m';
        }

        if ($diff->s) {
            $parts[] = $diff->s.'s';
        }

        return $parts ? implode(' ', $parts) : '0s';
    }

    /**
     * Calculate statistics for the RunTemplate.
     *
     * @return array Statistics data
     */
    private function calculateStats(): array
    {
        $jobber = new \MultiFlexi\Job();

        // Total jobs
        $totalJobs = (int) $jobber->listingQuery()
            ->where('runtemplate_id', $this->runtemplate->getMyKey())
            ->count();

        // Successful jobs
        $successfulJobs = (int) $jobber->listingQuery()
            ->where('runtemplate_id', $this->runtemplate->getMyKey())
            ->where('exitcode', 0)
            ->count();

        // Failed jobs
        $failedJobs = (int) $jobber->listingQuery()
            ->where('runtemplate_id', $this->runtemplate->getMyKey())
            ->where('exitcode IS NOT NULL')
            ->where('exitcode <> 0')
            ->count();

        // Running jobs
        $runningJobs = (int) $jobber->listingQuery()
            ->where('runtemplate_id', $this->runtemplate->getMyKey())
            ->where('begin IS NOT NULL')
            ->where('end IS NULL')
            ->count();

        // Calculate success rate
        $successRate = $totalJobs > 0 ? round(($successfulJobs / $totalJobs) * 100, 1) : 0;

        // Last run
        $lastRun = $jobber->listingQuery()
            ->where('runtemplate_id', $this->runtemplate->getMyKey())
            ->where('begin IS NOT NULL')
            ->orderBy('begin DESC')
            ->select('begin', true)
            ->limit(1)
            ->fetchColumn();

        return [
            'total_jobs' => $totalJobs,
            'successful_jobs' => $successfulJobs,
            'failed_jobs' => $failedJobs,
            'running_jobs' => $runningJobs,
            'success_rate' => $successRate,
            'last_run' => $lastRun ?: null,
        ];
    }

    /**
     * Create a compact statistics card with minimal padding.
     *
     * @param string $icon    Icon emoji or HTML
     * @param string $value   Main value to display
     * @param string $label   Card label
     * @param string $context Bootstrap context (primary, success, danger, etc.)
     */
    private static function createCompactStatCard(string $icon, string $value, string $label, string $context): \Ease\TWB4\Card
    {
        $card = new \Ease\TWB4\Card();
        $card->addTagClass('border-'.$context);
        $card->addTagClass('shadow-sm');

        // Compact card body with reduced padding
        $cardBody = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center p-1']);

        // Icon - smaller size
        $cardBody->addItem(new \Ease\Html\SpanTag($icon, ['style' => 'font-size: 1.1rem;']));

        // Value - prominent but not huge
        $valueDiv = new \Ease\Html\DivTag($value, ['class' => 'mb-0 text-'.$context, 'style' => 'font-weight: 600; font-size: 0.85rem;']);
        $cardBody->addItem($valueDiv);

        // Label - very small text
        $cardBody->addItem(new \Ease\Html\DivTag($label, ['class' => 'text-muted', 'style' => 'font-size: 0.6rem;']));

        $card->addItem($cardBody);

        return $card;
    }
}
