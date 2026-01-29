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

/**
 * Description of CompanyJobLister.
 *
 * @author vitex
 */
class CompanyJobLister extends CompanyJob
{
    /**
     * Filter type for job listing.
     */
    public ?string $filterType = null;

    /**
     * Array mapping job ID to queue position (1-based index).
     */
    private array $scheduledCounts = [];

    public function __construct($init = null, $filter = [])
    {
        parent::__construct($init, $filter);

        // Restore filter type from URL parameter if present
        if (isset($filter['_jobfilter'])) {
            $this->filterType = $filter['_jobfilter'];
        }
    }

    /**
     * Override to exclude _jobfilter from WHERE conditions.
     *
     * @param mixed $conditions
     */
    public function getAllForDataTable($conditions = [])
    {
        // Extract and remove _jobfilter before parent processing
        if (isset($conditions['_jobfilter'])) {
            $this->filterType = $conditions['_jobfilter'];
            unset($conditions['_jobfilter']);
        }

        return parent::getAllForDataTable($conditions);
    }

    /**
     * Apply custom filter to the listing query.
     *
     * @param string $filterType Filter type: 'success', 'failed', 'running', 'scheduled', 'today'
     */
    public function applyFilter(string $filterType): void
    {
        // Store filter type as property and in filter array for URL persistence
        $this->filterType = $filterType;
        $this->filter['_jobfilter'] = $filterType;
    }

    /**
     * columns to be selected from database.
     *
     * @return array
     */
    public function getColumns()
    {
        return ['id', 'company_id', 'app_id', 'env', 'exitcode', 'launched_by', 'begin', 'end', 'executor', 'schedule', 'schedule_type', 'runtemplate_id'];
    }

    /**
     * Columns.
     *
     * @param mixed $columns
     */

    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        /*
          +-------------+----------+------+-----+---------------------+----------------+
          | Field       | Type     | Null | Key | Default             | Extra          |
          +-------------+----------+------+-----+---------------------+----------------+
          | id          | int(11)  | NO   | PRI | NULL                | auto_increment |
          | app_id      | int(11)  | NO   | MUL | NULL                |                |
          | begin       | datetime | NO   |     | current_timestamp() |                |
          | end         | datetime | YES  |     | NULL                |                |
          | company_id  | int(11)  | NO   | MUL | NULL                |                |
          | exitcode    | int(11)  | YES  |     | NULL                |                |
          | stdout      | longblob | YES  |     | NULL                |                |
          | stderr      | text     | YES  |     | NULL                |                |
          | launched_by | text     | YES  |     | NULL                |                |
          | env         | text     | YES  |     | NULL                |                |
          +-------------+----------+------+-----+---------------------+----------------+
         */

        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'valueColumn' => 'job.id',
                'column' => 'job.id',
            ],
            ['name' => 'exitcode', 'type' => 'text', 'label' => _('Exit Code'),
                'valueColumn' => 'job.exitcode',
                'column' => 'job.exitcode',
            ],
            ['name' => 'app_id', 'type' => 'text', 'label' => _('App'),
                'valueColumn' => 'apps.name',
                'column' => 'apps.name',
            ],
            ['name' => 'runtemplate_id', 'type' => 'text', 'label' => _('RunTemplate'),
                'valueColumn' => 'runtemplate.name',
                'column' => 'runtemplate.name',
            ],
            ['name' => 'begin', 'type' => 'datetime', 'label' => _('Launch time'),
                'column' => 'job.begin',
            ],
            ['name' => 'launched_by', 'type' => 'text', 'label' => _('Launcher'),
                'valueColumn' => 'user.login',
                'column' => 'user.login',
            ],
            ['name' => 'company_id', 'type' => 'text', 'label' => _('Company'),
                'valueColumn' => 'company.name',
                'column' => 'company.name',
            ],
        ]);
    }

    public function addSelectizeValues($query)
    {
        // Get current language (first 2 chars from locale, e.g. 'en' from 'en_US')
        $currentLang = substr(\Ease\Locale::$localeUsed ?? 'en_US', 0, 2);

        // Build queue position map for all scheduled jobs
        $scheduler = new \MultiFlexi\Scheduler();
        $scheduledJobsQuery = $scheduler->listingQuery()->select('schedule.job, schedule.after')->orderBy('schedule.after ASC');
        // Disable smart join to prevent FluentPDO from trying to auto-join on schedule_id
        $scheduledJobsQuery->disableSmartJoin();
        $scheduledJobs = $scheduledJobsQuery->fetchAll();
        $this->scheduledCounts = [];
        $position = 1;

        foreach ($scheduledJobs as $scheduledJob) {
            $this->scheduledCounts[$scheduledJob['job']] = $position++;
        }

        $query->select(['apps.name AS appname', 'apps.uuid', 'apps.image AS appimage', 'apps.description AS appdescription', 'apps.homepage AS apphomepage', 'app_translations.description AS appdescription_localized', 'job.id', 'begin', 'end', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'job.executor', 'job.company_id', 'company.name', 'company.logo', 'company.ic', 'company.enabled', 'schedule', 'schedule_type', 'job.runtemplate_id', 'runtemplate.name AS runtemplate_name', 'runtemplate.note AS runtemplate_note', 'runtemplate.interv AS runtemplate_interv', 'runtemplate.cron AS runtemplate_cron', 'runtemplate.last_schedule AS runtemplate_last_schedule', 'runtemplate.next_schedule AS runtemplate_next_schedule', 'runtemplate.delay AS runtemplate_delay', 'schedule.id AS schedule_id'], true)
            ->leftJoin('apps ON apps.id = job.app_id')
            ->leftJoin('app_translations ON app_translations.app_id = apps.id AND app_translations.lang = ?', $currentLang)
            ->leftJoin('company ON company.id = job.company_id')
            ->leftJoin('user ON user.id = job.launched_by')
            ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')
            ->leftJoin('schedule ON schedule.job = job.id');

        // Apply special filters based on filterType
        if (!empty($this->filterType)) {
            switch ($this->filterType) {
                case 'success':
                    $query->where('job.exitcode', 0);

                    break;
                case 'failed':
                    $query->where('(job.exitcode <> 0 AND job.exitcode <> "0")');
                    $query->where('job.exitcode IS NOT NULL');

                    break;
                case 'running':
                    $query->where('job.begin IS NOT NULL')->where('job.end IS NULL');

                    break;
                case 'scheduled':
                    // Jobs that are scheduled but not yet started (begin IS NULL and schedule IS NOT NULL)
                    $query->where('job.begin IS NULL')->where('job.schedule IS NOT NULL');

                    break;
                case 'today':
                    $jobber = new \MultiFlexi\Job();
                    $todayCondition = $jobber->todaysCond('job.begin');
                    $query->where($todayCondition);

                    break;
            }
        }

        return parent::addSelectizeValues($query);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $exitCode = $dataRowRaw['exitcode'] ?? null;
        $jobId = $dataRowRaw['id'];

        // Format ID column as link (compact)
        $dataRowRaw['id'] = sprintf(
            '<a href="job.php?id=%d" style="font-size: 0.95em; font-weight: bold; color: #000; text-decoration: none; white-space: nowrap;">🏁%d</a>',
            $jobId,
            $jobId,
        );

        // Set row background color based on job status
        // Check if job is scheduled (not yet started)
        $isScheduled = !empty($dataRowRaw['schedule_id']);

        if (empty($dataRowRaw['begin']) && $isScheduled) {
            $dataRowRaw['DT_RowClass'] = 'job-scheduled';
        } elseif (empty($dataRowRaw['begin']) && !empty($dataRowRaw['schedule'])) {
            // Orphaned job - scheduled but no schedule entry
            $dataRowRaw['DT_RowClass'] = 'job-orphaned bg-warning';
        } else {
            // Set row background color based on exit code for executed jobs
            switch ($exitCode) {
                case '0':
                    $dataRowRaw['DT_RowClass'] = 'job-success';

                    break;
                case '1':
                    $dataRowRaw['DT_RowClass'] = 'bg-warning text-dark';

                    break;
                case '255':
                    $dataRowRaw['DT_RowClass'] = 'bg-danger text-dark';

                    break;
                case '127':
                    $dataRowRaw['DT_RowClass'] = 'bg-primary text-white';

                    break;
                case '-1':
                    $dataRowRaw['DT_RowClass'] = 'bg-info text-white';

                    break;

                default:
                    $dataRowRaw['DT_RowClass'] = 'text-dark';

                    break;
            }
        }

        // Format Exit Code column (simple, just the code)
        $exitCodeWidget = new \MultiFlexi\Ui\ExitCode($exitCode, ['style' => 'font-size: 0.9em; font-family: monospace;']);
        $dataRowRaw['exitcode'] = $exitCodeWidget->__toString();

        // Format Application column with icon and rich popover
        if (isset($dataRowRaw['appname'])) {
            $appImageUrl = empty($dataRowRaw['appimage']) ? 'appimage.php?uuid='.$dataRowRaw['uuid'] : $dataRowRaw['appimage'];

            // Build rich popover content for application with large logo, description, and homepage
            $appPopoverContent = '<div style="font-size: 0.9em; max-width: 300px;">';

            // Large logo centered at top
            $appPopoverContent .= '<div style="text-align: center; margin-bottom: 10px;">';
            $appPopoverContent .= '<img src="'.htmlspecialchars($appImageUrl).'" alt="'.htmlspecialchars($dataRowRaw['appname']).'" style="max-width: 80px; max-height: 80px;">';
            $appPopoverContent .= '</div>';

            // Application name
            $appPopoverContent .= '<div style="text-align: center; margin-bottom: 8px;">';
            $appPopoverContent .= '<strong style="font-size: 1.1em;">'.htmlspecialchars($dataRowRaw['appname']).'</strong>';
            $appPopoverContent .= '</div>';

            // Description if available - prefer localized version
            $description = !empty($dataRowRaw['appdescription_localized'])
                ? $dataRowRaw['appdescription_localized']
                : $dataRowRaw['appdescription'] ?? '';

            if (!empty($description)) {
                $appPopoverContent .= '<p style="margin: 8px 0; line-height: 1.4; color: #555;">'.htmlspecialchars($description).'</p>';
            }

            // Homepage link if available
            if (!empty($dataRowRaw['apphomepage'])) {
                $appPopoverContent .= '<div style="text-align: center; margin-top: 10px;">';
                $appPopoverContent .= '<a href="'.htmlspecialchars($dataRowRaw['apphomepage']).'" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size: 0.85em;">🏠 '.htmlspecialchars(_('Homepage')).'</a>';
                $appPopoverContent .= '</div>';
            }

            $appPopoverContent .= '</div>';

            $dataRowRaw['app_id'] = sprintf(
                '<a href="app.php?id=%d" tabindex="0" data-toggle="popover" data-trigger="hover focus" data-placement="right" data-html="true" data-content="%s" data-container="body"><img src="%s" height="24" alt="%s" style="vertical-align: middle;"></a>',
                $dataRowRaw['app_id'],
                htmlspecialchars($appPopoverContent),
                htmlspecialchars($appImageUrl),
                htmlspecialchars($dataRowRaw['appname']),
            );
        }

        // Format Launch time column (compact - single line)
        if (empty($dataRowRaw['begin']) === false) { // Job already started
            try {
                $beginTime = new \DateTime($dataRowRaw['begin']);
                $relativeTime = self::getRelativeTime($beginTime);
                $dataRowRaw['begin'] = sprintf(
                    '<span title="%s" style="font-size: 0.85em; white-space: nowrap;">%s</span>',
                    htmlspecialchars($dataRowRaw['begin']),
                    htmlspecialchars($relativeTime),
                );
            } catch (\Exception $e) {
                $dataRowRaw['begin'] = '<span style="font-size: 0.85em;">'.htmlspecialchars($dataRowRaw['begin']).'</span>';
            }
        } else {
            if (empty($dataRowRaw['schedule']) === false) {
                try {
                    $scheduleTime = new \DateTime($dataRowRaw['schedule']);
                    $relativeTime = self::getRelativeTime($scheduleTime);

                    // Check if job is in queue (has schedule_id)
                    $queueInfo = '';

                    if ($isScheduled && isset($this->scheduledCounts[$jobId])) {
                        $queuePosition = $this->scheduledCounts[$jobId];
                        $totalInQueue = \count($this->scheduledCounts);
                        $queueInfo = sprintf(' <span class="badge badge-info" style="font-size: 0.7em;">#%d/%d</span>', $queuePosition, $totalInQueue);
                    } elseif (!$isScheduled) {
                        // Orphaned - show clickable warning badge linking to reschedule page
                        $queueInfo = sprintf(' <a href="reschedule.php?job_id=%d" class="badge badge-warning" style="font-size: 0.7em; text-decoration: none;" title="%s">⚠️ orphaned</a>', $jobId, htmlspecialchars(_('Click to re-schedule this job')));
                    }

                    $dataRowRaw['begin'] = sprintf(
                        '💣 <span title="%s" style="font-size: 0.85em; white-space: nowrap;">%s</span>%s',
                        htmlspecialchars($dataRowRaw['schedule']),
                        htmlspecialchars($relativeTime),
                        $queueInfo,
                    );
                } catch (\Exception $e) {
                    $dataRowRaw['begin'] = '💣 <span style="font-size: 0.85em;">'.htmlspecialchars($dataRowRaw['schedule']).'</span>';
                }
            } else {
                $dataRowRaw['begin'] = '<span style="font-size: 0.85em; color: #999;">—</span>';
            }
        }

        // Format Launcher column with interval/cron information
        $executorImg = !empty($dataRowRaw['executor'])
            ? (new \MultiFlexi\Ui\ExecutorImage($dataRowRaw['executor'], ['height' => 20, 'style' => 'vertical-align: middle; margin-right: 4px;']))->__toString()
            : '';

        // Determine launcher info
        if (!empty($dataRowRaw['launched_by']) && !empty($dataRowRaw['login'])) {
            // Manual launch by user
            $launcherInfo = sprintf('<a href="user.php?id=%d" style="font-size: 0.85em;">%s</a>', $dataRowRaw['launched_by'], htmlspecialchars($dataRowRaw['login']));
        } else {
            // Automatic launch - show interval or cron
            $runtemplateInterv = $dataRowRaw['runtemplate_interv'] ?? '';
            $runtemplateCron = $dataRowRaw['runtemplate_cron'] ?? '';

            if ($runtemplateInterv && $runtemplateInterv !== 'n' && $runtemplateInterv !== 'c') {
                // Named interval
                $intervalEmoji = \MultiFlexi\Scheduler::getIntervalEmoji($runtemplateInterv);
                $intervalName = \MultiFlexi\Scheduler::codeToInterval($runtemplateInterv);
                $launcherInfo = sprintf(
                    '<span style="font-size: 0.85em; color: #666;" title="%s">%s %s</span>',
                    htmlspecialchars($intervalName),
                    $intervalEmoji,
                    htmlspecialchars($intervalName),
                );
            } elseif ($runtemplateInterv === 'c' && !empty($runtemplateCron)) {
                // Cron expression
                $launcherInfo = sprintf(
                    '<span style="font-size: 0.75em; font-family: monospace; color: #666;" title="Cron: %s">⏰ %s</span>',
                    htmlspecialchars($runtemplateCron),
                    htmlspecialchars($runtemplateCron),
                );
            } else {
                $launcherInfo = '<span style="font-size: 0.85em; color: #666;">Timer</span>';
            }
        }

        $scheduleTypeInfo = !empty($dataRowRaw['schedule_type']) ?
                '<span style="font-size: 0.75em; color: #888; margin-left: 4px;" title="Schedule type">('.htmlspecialchars($dataRowRaw['schedule_type']).')</span>' : '';

        $dataRowRaw['launched_by'] = $executorImg.$launcherInfo.$scheduleTypeInfo;

        // Format Runtemplate column with number + name and detailed tooltip
        if (isset($dataRowRaw['runtemplate_id']) && $dataRowRaw['runtemplate_id']) {
            $runtemplateId = $dataRowRaw['runtemplate_id'];
            $runtemplateName = $dataRowRaw['runtemplate_name'] ?? 'RunTemplate';
            $runtemplateInterv = $dataRowRaw['runtemplate_interv'] ?? '';
            $runtemplateLastSchedule = $dataRowRaw['runtemplate_last_schedule'] ?? '';
            $runtemplateNextSchedule = $dataRowRaw['runtemplate_next_schedule'] ?? '';
            $runtemplateDelay = $dataRowRaw['runtemplate_delay'] ?? 0;

            // Build tooltip with details
            $intervalEmoji = \MultiFlexi\Scheduler::getIntervalEmoji($runtemplateInterv);
            $intervalName = \MultiFlexi\Scheduler::codeToInterval($runtemplateInterv);

            $tooltipParts = [];
            $tooltipParts[] = 'Name: '.htmlspecialchars($runtemplateName);

            if ($runtemplateInterv) {
                $tooltipParts[] = 'Interval: '.$intervalEmoji.' '.htmlspecialchars($intervalName);
            }

            if ($runtemplateLastSchedule) {
                try {
                    $lastScheduleTime = new \DateTime($runtemplateLastSchedule);
                    $lastScheduleRelative = self::getRelativeTime($lastScheduleTime);
                    $tooltipParts[] = 'Last scheduled: '.$lastScheduleRelative;
                } catch (\Exception $e) {
                    $tooltipParts[] = 'Last scheduled: '.htmlspecialchars($runtemplateLastSchedule);
                }
            }

            if ($runtemplateNextSchedule) {
                try {
                    $nextScheduleTime = new \DateTime($runtemplateNextSchedule);
                    $nextScheduleRelative = self::getRelativeTime($nextScheduleTime);
                    $tooltipParts[] = 'Next schedule: '.$nextScheduleRelative;
                } catch (\Exception $e) {
                    $tooltipParts[] = 'Next schedule: '.htmlspecialchars($runtemplateNextSchedule);
                }
            }

            if ($runtemplateDelay > 0) {
                $delayMinutes = round($runtemplateDelay / 60);
                $tooltipParts[] = 'Delay: '.$delayMinutes.' min';
            }

            // Build rich popover content for runtemplate
            $rtPopoverContent = '<div style="font-size: 0.9em;">';
            $rtPopoverContent .= '<strong>'.htmlspecialchars($runtemplateName).'</strong><br>';

            // Show note if exists
            if (!empty($dataRowRaw['runtemplate_note'])) {
                // Strip HTML tags from note to display as plain text
                $noteText = strip_tags($dataRowRaw['runtemplate_note']);

                if (!empty($noteText)) {
                    $rtPopoverContent .= '<p style="margin: 8px 0; font-style: italic; color: #666; line-height: 1.4;">'.htmlspecialchars($noteText).'</p>';
                }
            }

            if ($runtemplateInterv) {
                $rtPopoverContent .= '<span class="badge badge-info">'.$intervalEmoji.' '.htmlspecialchars($intervalName).'</span><br>';
            }

            if ($runtemplateLastSchedule) {
                try {
                    $lastScheduleTime = new \DateTime($runtemplateLastSchedule);
                    $lastScheduleRelative = self::getRelativeTime($lastScheduleTime);
                    $rtPopoverContent .= '<small class="text-muted">Last: '.htmlspecialchars($lastScheduleRelative).'</small><br>';
                } catch (\Exception $e) {
                }
            }

            if ($runtemplateNextSchedule) {
                try {
                    $nextScheduleTime = new \DateTime($runtemplateNextSchedule);
                    $nextScheduleRelative = self::getRelativeTime($nextScheduleTime);
                    $rtPopoverContent .= '<small class="text-success">Next: '.htmlspecialchars($nextScheduleRelative).'</small>';
                } catch (\Exception $e) {
                }
            }

            if ($runtemplateDelay > 0) {
                $delayMinutes = round($runtemplateDelay / 60);
                $rtPopoverContent .= '<br><small class="text-warning">Delay: '.$delayMinutes.' min</small>';
            }

            $rtPopoverContent .= '</div>';

            // Truncate name if too long
            $displayName = mb_strlen($runtemplateName) > 40 ? mb_substr($runtemplateName, 0, 37).'...' : $runtemplateName;

            // Format ID with fixed width (4 digits, right-aligned with monospace font)
            $dataRowRaw['runtemplate_id'] = sprintf(
                '<a href="runtemplate.php?id=%d" style="font-size: 0.85em; white-space: nowrap;" tabindex="0" data-toggle="popover" data-trigger="hover focus" data-placement="right" data-html="true" data-content="%s" data-container="body">⚗️<span style="font-family: monospace; display: inline-block; width: 3em; text-align: right;">#%d</span> <span style="color: #666;">%s</span></a>',
                $runtemplateId,
                htmlspecialchars($rtPopoverContent),
                $runtemplateId,
                htmlspecialchars($displayName),
            );
        } else {
            $dataRowRaw['runtemplate_id'] = '<span style="font-size: 0.85em; color: #999;">—</span>';
        }

        // Format Company column with rich popover
        if (isset($dataRowRaw['company_id'])) {
            // Build rich popover content for company with large logo and details
            $companyPopoverContent = '<div style="font-size: 0.9em; max-width: 300px;">';

            // Large logo centered at top
            if (!empty($dataRowRaw['logo'])) {
                $companyPopoverContent .= '<div style="text-align: center; margin-bottom: 10px;">';
                $companyPopoverContent .= '<img src="'.htmlspecialchars($dataRowRaw['logo']).'" alt="'.htmlspecialchars($dataRowRaw['name'] ?? '').'" style="max-width: 80px; max-height: 80px;">';
                $companyPopoverContent .= '</div>';
            }

            // Company name
            $companyPopoverContent .= '<div style="text-align: center; margin-bottom: 8px;">';
            $companyPopoverContent .= '<strong style="font-size: 1.1em;">'.htmlspecialchars($dataRowRaw['name'] ?? '').'</strong>';
            $companyPopoverContent .= '</div>';

            // Company details
            if (!empty($dataRowRaw['ic'])) {
                $companyPopoverContent .= '<p style="margin: 4px 0;"><small class="text-muted">IČ: '.htmlspecialchars($dataRowRaw['ic']).'</small></p>';
            }

            // Status badge
            if (isset($dataRowRaw['enabled'])) {
                $statusBadge = $dataRowRaw['enabled']
                    ? '<span class="badge badge-success">✓ '.htmlspecialchars(_('Enabled')).'</span>'
                    : '<span class="badge badge-secondary">✗ '.htmlspecialchars(_('Disabled')).'</span>';
                $companyPopoverContent .= '<div style="text-align: center; margin-top: 8px;">'.$statusBadge.'</div>';
            }

            $companyPopoverContent .= '</div>';

            // Small logo for table cell
            $companyLogo = !empty($dataRowRaw['logo']) ?
                    sprintf(
                        '<img src="%s" height="24" alt="%s" style="vertical-align: middle; margin-right: 4px;">',
                        htmlspecialchars($dataRowRaw['logo']),
                        htmlspecialchars($dataRowRaw['name'] ?? ''),
                    ) : '';
            $companyName = htmlspecialchars($dataRowRaw['name'] ?? '');
            $dataRowRaw['company_id'] = sprintf(
                '<a href="company.php?id=%d" style="display: inline-flex; align-items: center; font-size: 0.85em; white-space: nowrap;" tabindex="0" data-toggle="popover" data-trigger="hover focus" data-placement="right" data-html="true" data-content="%s" data-container="body">%s<span>%s</span></a>',
                $dataRowRaw['company_id'],
                htmlspecialchars($companyPopoverContent),
                $companyLogo,
                $companyName,
            );
        }

        return $dataRowRaw;
    }

    public function tableCode($tableId)
    {
        return <<<'EOD'

 "order": [[ 0, "desc" ]],

EOD;
    }

    /**
     * Get relative time string (e.g., "2 hours ago" or "in 5 hours").
     * Automatically detects if the datetime is in the future or past.
     *
     * @param \DateTime $dateTime The datetime to compare with current time
     *
     * @return string Localized relative time string
     */
    public static function getRelativeTime(\DateTime $dateTime): string
    {
        $now = new \DateTime();
        $diff = $now->diff($dateTime);

        // Determine if the date is actually in the future or past
        $isFuture = ($dateTime > $now);

        if ($diff->y > 0) {
            return $isFuture
                ? sprintf(_('in %d %s'), $diff->y, $diff->y === 1 ? _('year') : _('years'))
                : $diff->y.' '.($diff->y === 1 ? _('year ago') : _('years ago'));
        }

        if ($diff->m > 0) {
            return $isFuture
                ? sprintf(_('in %d %s'), $diff->m, $diff->m === 1 ? _('month') : _('months'))
                : $diff->m.' '.($diff->m === 1 ? _('month ago') : _('months ago'));
        }

        if ($diff->d > 0) {
            return $isFuture
                ? sprintf(_('in %d %s'), $diff->d, $diff->d === 1 ? _('day') : _('days'))
                : $diff->d.' '.($diff->d === 1 ? _('day ago') : _('days ago'));
        }

        if ($diff->h > 0) {
            return $isFuture
                ? sprintf(_('in %d %s'), $diff->h, $diff->h === 1 ? _('hour') : _('hours'))
                : $diff->h.' '.($diff->h === 1 ? _('hour ago') : _('hours ago'));
        }

        if ($diff->i > 0) {
            return $isFuture
                ? sprintf(_('in %d %s'), $diff->i, $diff->i === 1 ? _('minute') : _('minutes'))
                : $diff->i.' '.($diff->i === 1 ? _('minute ago') : _('minutes ago'));
        }

        return _('just now');
    }
}
