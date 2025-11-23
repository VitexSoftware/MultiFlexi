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

        // Main statistics row - 4 compact cards
        $cardsRow = new \Ease\TWB4\Row();
        $cardsRow->addTagClass('mb-3');

        // Total Jobs Card
        $cardsRow->addColumn(3, self::createCompactStatCard(
            '📊',
            (string) $this->stats['total_jobs'],
            _('Total Jobs'),
            'primary',
        ));

        // Success Rate Card
        $cardsRow->addColumn(3, self::createCompactStatCard(
            '✅',
            $this->stats['success_rate'].'%',
            _('Success Rate'),
            $this->stats['success_rate'] >= 80 ? 'success' : ($this->stats['success_rate'] >= 50 ? 'warning' : 'danger'),
        ));

        // Failed Jobs Card
        $cardsRow->addColumn(3, self::createCompactStatCard(
            '❌',
            (string) $this->stats['failed_jobs'],
            _('Failed Jobs'),
            'danger',
        ));

        // Running Jobs Card
        $cardsRow->addColumn(3, self::createCompactStatCard(
            '🔄',
            (string) $this->stats['running_jobs'],
            _('Running Jobs'),
            'info',
        ));

        $this->addItem($cardsRow);

        // Timing information and metadata block
        $metaWrap = new \Ease\Html\DivTag(null, ['class' => 'p-2 bg-light rounded mb-2', 'style' => 'font-size: 0.85rem;']);

        // 1) Timing: last run / last schedule / next schedule
        $timingParts = [];

        if ($this->stats['last_run']) {
            $lastRunDate = new \DateTime($this->stats['last_run']);
            $timingParts[] = '⏱️ '._('Last Run').': '.$lastRunDate->format('Y-m-d H:i');
        }

        if ($this->runtemplate->getDataValue('last_schedule')) {
            $lastScheduleDate = new \DateTime($this->runtemplate->getDataValue('last_schedule'));
            $timingParts[] = '📅 '._('Last Schedule').': '.$lastScheduleDate->format('Y-m-d H:i');
        }

        if ($this->runtemplate->getDataValue('next_schedule')) {
            $nextScheduleDate = new \DateTime($this->runtemplate->getDataValue('next_schedule'));
            $timingParts[] = '⏰ '._('Next Schedule').': '.$nextScheduleDate->format('Y-m-d H:i');
        }

        if ($timingParts) {
            $metaWrap->addItem(implode(' | ', $timingParts));
        }

        // 2) Created / Updated with relative age
        $createdRaw = (string) $this->runtemplate->getDataValue($this->runtemplate->createColumn);
        $updatedRaw = (string) $this->runtemplate->getDataValue($this->runtemplate->lastModifiedColumn);

        if ($createdRaw || $updatedRaw) {
            if ($timingParts) {
                $metaWrap->addItem('<br>');
            }

            if ($createdRaw) {
                $cd = new \DateTime($createdRaw);
                $metaWrap->addItem('📅 '._('Created').': '.$cd->format('Y-m-d H:i').' - '.self::formatAge($cd));
            }

            if ($updatedRaw) {
                if ($createdRaw) {
                    $metaWrap->addItem(' | ');
                }

                $ud = new \DateTime($updatedRaw);
                $metaWrap->addItem('💾 '._('Updated').': '.$ud->format('Y-m-d H:i').' - '.self::formatAge($ud));
            }
        }

        // 3) Other meaningful metadata chips
        $chips = [];
        $active = (bool) $this->runtemplate->getDataValue('active');
        $chips[] = ($active ? '🟢 '._('Enabled') : '🔴 '._('Disabled'));

        $interval = (string) $this->runtemplate->getDataValue('interv');

        if ($interval !== '') {
            $chips[] = '⏳ '._('Interval').': '.self::formatInterval($interval);
        }

        $cron = (string) $this->runtemplate->getDataValue('cron');

        if ($cron !== '') {
            $chips[] = '🧭 Cron: '.$cron;
        }

        $delay = (int) ($this->runtemplate->getDataValue('delay') ?? 0);
        $chips[] = '⏱ '._('Startup Delay').': '.self::formatDuration($delay);

        $executor = (string) $this->runtemplate->getDataValue('executor');

        if ($executor !== '') {
            $chips[] = '⚙️ '._('Executor').': '.$executor;
        }

        if ($chips) {
            $metaWrap->addItem('<br>');
            $metaWrap->addItem(implode(' • ', $chips));
        }

        $this->addItem($metaWrap);
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
        $cardBody = new \Ease\Html\DivTag(null, ['class' => 'card-body text-center p-2']);

        // Icon - smaller size
        $cardBody->addItem(new \Ease\Html\SpanTag($icon, ['style' => 'font-size: 1.5rem;']));

        // Value - prominent but not huge
        $valueDiv = new \Ease\Html\DivTag($value, ['class' => 'h5 mb-0 mt-1 text-'.$context, 'style' => 'font-weight: 600;']);
        $cardBody->addItem($valueDiv);

        // Label - very small text
        $cardBody->addItem(new \Ease\Html\DivTag($label, ['class' => 'text-muted', 'style' => 'font-size: 0.7rem;']));

        $card->addItem($cardBody);

        return $card;
    }
}
