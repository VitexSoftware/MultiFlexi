<?php

/**
 * MultiFlexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of CompanyJobLister
 *
 * @author vitex
 */
class CompanyJobLister extends CompanyJob {
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
            ['name' => 'id', 'type' => 'text', 'label' => _('ID úlohy'),
                'valueColumn' => 'job.id',
                'column' => 'job.id',
            ],
            ['name' => 'app_id', 'type' => 'text', 'label' => _('Application'),
                'valueColumn' => 'apps.name',
                'column' => 'apps.name',
            ],
            ['name' => 'exitcode', 'type' => 'text', 'label' => _('Exit Code').'/'._('Job ID'),
                'valueColumn' => 'job.id',
                'column' => 'job.id',
            ],
            ['name' => 'runtemplate_id', 'type' => 'text', 'label' => _('Runtemplate'),
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
        $query->select(['apps.name AS appname', 'apps.uuid', 'apps.image AS appimage', 'job.id', 'begin', 'end', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'job.executor', 'job.company_id', 'company.name', 'company.logo', 'schedule', 'schedule_type', 'job.runtemplate_id', 'runtemplate.name AS runtemplate_name'], true)
            ->leftJoin('apps ON apps.id = job.app_id')
            ->leftJoin('company ON company.id = job.company_id')
            ->leftJoin('user ON user.id = job.launched_by')
            ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id');

        return parent::addSelectizeValues($query);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $exitCode = $dataRowRaw['exitcode'] ?? null;
        $jobId = $dataRowRaw['id'];
        
        // Format ID column as link
        $dataRowRaw['id'] = sprintf(
            '<a href="job.php?id=%d" style="font-size: 1.5em; font-weight: bold; color: #000; text-decoration: none;">🏁 %d</a>',
            $jobId,
            $jobId
        );
        
        // Set row background color based on exit code
        switch ($exitCode) {
            case '0':
                $dataRowRaw['DT_RowClass'] = 'bg-success text-white';
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

        // Format Application column with icon and name
        if (isset($dataRowRaw['appname'])) {
            $appImageUrl = empty($dataRowRaw['appimage']) ? 'appimage.php?uuid='.$dataRowRaw['uuid'] : $dataRowRaw['appimage'];
            $dataRowRaw['app_id'] = sprintf(
                '<a href="app.php?id=%d"><span class="badge badge-light"><img src="%s" height="60" title="%s" alt="%s"> %s</span></a>',
                $dataRowRaw['app_id'],
                htmlspecialchars($appImageUrl),
                htmlspecialchars($dataRowRaw['appname']),
                htmlspecialchars($dataRowRaw['appname']),
                htmlspecialchars($dataRowRaw['appname'])
            );
        }

        // Format Exit Code / Job ID column
        $exitCodeWidget = New \Ease\Html\ATag('job.php?id='.$jobId, new \MultiFlexi\Ui\ExitCode($exitCode, ['style' => 'font-size: 1.0em; font-family: monospace;']));
        $dataRowRaw['exitcode'] = $exitCodeWidget->__toString();
        

        // Format Launch time column
        if (!empty($dataRowRaw['begin'])) {
            try {
                $beginTime = new \DateTime($dataRowRaw['begin']);
                $dataRowRaw['begin'] = $dataRowRaw['begin'] . '<br><small>' . $this->getRelativeTime($beginTime) . '</small>';
            } catch (\Exception $e) {
                // Keep original value if parsing fails
            }
        } else {
            $scheduleDisplay = '';
            if (!empty($dataRowRaw['schedule'])) {
                try {
                    $scheduleTime = new \DateTime($dataRowRaw['schedule']);
                    $scheduleDisplay = '<div>' . $this->getRelativeTime($scheduleTime) . '</div>';
                } catch (\Exception $e) {
                    // Ignore parsing error
                }
            }
            $dataRowRaw['begin'] = '⏳' . $scheduleDisplay;
        }

        // Format Launcher column
        $executorImg = !empty($dataRowRaw['executor']) ? sprintf(
            '<img src="images/executor/%s.svg" align="right" height="50px" alt="%s">',
            htmlspecialchars($dataRowRaw['executor']),
            htmlspecialchars($dataRowRaw['executor'])
        ) : '';
        
        $userBadge = !empty($dataRowRaw['launched_by']) && !empty($dataRowRaw['login']) ? 
            sprintf('<a href="user.php?id=%d"><span class="badge badge-info">%s</span></a>', $dataRowRaw['launched_by'], htmlspecialchars($dataRowRaw['login'])) : 
            'Timer';
        
        $scheduleInfo = !empty($dataRowRaw['schedule']) ? '<div>' . htmlspecialchars($dataRowRaw['schedule']) . '</div>' : '';
        $executorInfo = !empty($dataRowRaw['executor']) || !empty($dataRowRaw['schedule_type']) ? 
            '<div>' . htmlspecialchars($dataRowRaw['executor'] ?? '') . ' ' . htmlspecialchars($dataRowRaw['schedule_type'] ?? '') . '</div>' : '';
        
        $dataRowRaw['launched_by'] = $executorImg . '<div>' . $userBadge . '</div>' . $scheduleInfo . $executorInfo;

        // Format Runtemplate column
        if (isset($dataRowRaw['runtemplate_id']) && $dataRowRaw['runtemplate_id']) {
            $runtemplateId = $dataRowRaw['runtemplate_id'];
            $runtemplateName = htmlspecialchars($dataRowRaw['runtemplate_name'] ?? '⚗️ #' . $runtemplateId);
            $dataRowRaw['runtemplate_id'] = sprintf(
                '<a href="runtemplate.php?id=%d" style="color: black;">⚗️ #%s %s</a>',
                $runtemplateId,
                $runtemplateId,
                $runtemplateName
            );
        } else {
            $dataRowRaw['runtemplate_id'] = '';
        }

        // Format Company column
        if (isset($dataRowRaw['company_id'])) {
            $companyLogo = !empty($dataRowRaw['logo']) ? 
                sprintf('<img src="%s" height="60px" align="right" alt="Company Logo">', htmlspecialchars($dataRowRaw['logo'])) : '';
            $companyName = htmlspecialchars($dataRowRaw['name'] ?? '');
            $dataRowRaw['company_id'] = sprintf(
                '<a href="company.php?id=%d">%s</a><a href="company.php?id=%d">%s</a>',
                $dataRowRaw['company_id'],
                $companyLogo,
                $dataRowRaw['company_id'],
                $companyName
            );
        }

        return $dataRowRaw;
    }

    /**
     * Get relative time string (e.g., "2 hours ago").
     *
     * @param \DateTime $dateTime
     * @return string
     */
    private function getRelativeTime(\DateTime $dateTime): string
    {
        $now = new \DateTime();
        $diff = $now->diff($dateTime);
        
        if ($diff->y > 0) {
            return $diff->y . ' ' . ($diff->y === 1 ? _('year ago') : _('years ago'));
        } elseif ($diff->m > 0) {
            return $diff->m . ' ' . ($diff->m === 1 ? _('month ago') : _('months ago'));
        } elseif ($diff->d > 0) {
            return $diff->d . ' ' . ($diff->d === 1 ? _('day ago') : _('days ago'));
        } elseif ($diff->h > 0) {
            return $diff->h . ' ' . ($diff->h === 1 ? _('hour ago') : _('hours ago'));
        } elseif ($diff->i > 0) {
            return $diff->i . ' ' . ($diff->i === 1 ? _('minute ago') : _('minutes ago'));
        } else {
            return _('just now');
        }
    }

    public function tableCode($tableId)
    {
        return <<<'EOD'

 "order": [[ 1, "asc" ]],

EOD;
    }
}
