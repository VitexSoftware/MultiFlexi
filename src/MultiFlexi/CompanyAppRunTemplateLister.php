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
 * RunTemplate Lister filtered by Company and Application.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CompanyAppRunTemplateLister extends RunTemplate
{
    /**
     * Company ID filter.
     */
    private ?int $companyId = null;

    /**
     * Application ID filter.
     */
    private ?int $appId = null;

    /**
     * Set company filter.
     *
     * @param int|Company $company Company ID or Company object
     */
    public function setCompany($company): self
    {
        if ($company instanceof Company) {
            $this->companyId = (int) $company->getMyKey();
        } else {
            $this->companyId = (int) $company;
        }

        return $this;
    }

    /**
     * Set application filter.
     *
     * @param int|Application $app Application ID or Application object
     */
    public function setApp($app): self
    {
        if ($app instanceof Application) {
            $this->appId = (int) $app->getMyKey();
        } else {
            $this->appId = (int) $app;
        }

        return $this;
    }

    public function columns($columns = [])
    {
        // Return simple column definitions without parent call
        $this->columnsCache = [
            'id' => ['name' => 'id', 'type' => 'text', 'label' => _('ID'), 'column' => 'runtemplate.id'],
            'active' => ['name' => 'active', 'type' => 'boolean', 'label' => _('Active'), 'column' => 'runtemplate.active'],
            'interv' => ['name' => 'interv', 'type' => 'text', 'label' => _('Interval'), 'column' => 'runtemplate.interv'],
            'name' => ['name' => 'name', 'type' => 'text', 'label' => _('Name'), 'column' => 'runtemplate.name'],
            'last_job' => ['name' => 'last_job', 'type' => 'text', 'label' => _('Last Job'), 'searchable' => false, 'orderable' => false],
            'actions' => ['name' => 'actions', 'type' => 'text', 'label' => _('Actions'), 'searchable' => false, 'orderable' => false],
            'executor' => ['name' => 'executor', 'type' => 'text', 'label' => _('Executor'), 'column' => 'runtemplate.executor'],
        ];
        
        return $this->columnsCache;
    }

    public function listingQuery(): \Envms\FluentPDO\Queries\Select
    {
        // Create query without any automatic JOINs
        $query = $this->getFluentPDO()->from('runtemplate');
        
        // Disable smart joins to prevent automatic relationship detection
        $query->disableSmartJoin();
        
        // Apply filters immediately
        if ($this->companyId !== null) {
            $query->where('runtemplate.company_id', $this->companyId);
        }

        if ($this->appId !== null) {
            $query->where('runtemplate.app_id', $this->appId);
        }
        
        return $query;
    }

    /**
     * Override to prevent automatic JOINs that DBEngine might try to add.
     *
     * @param array $conditions
     *
     * @return array
     */
    public function getAllForDataTable($conditions = [])
    {
        $draw = \array_key_exists('draw', $conditions) ? (int) $conditions['draw'] : 1;
        $columns = \array_key_exists('columns', $conditions) ? $conditions['columns'] : [];
        $order = \array_key_exists('order', $conditions) ? $conditions['order'] : [];
        $start = \array_key_exists('start', $conditions) ? (int) $conditions['start'] : 0;
        $length = \array_key_exists('length', $conditions) ? (int) $conditions['length'] : 10;
        $search = \array_key_exists('search', $conditions) && \array_key_exists('value', $conditions['search']) ? $conditions['search']['value'] : '';

        // Get base query from listingQuery() which has our filters
        $query = $this->listingQuery();
        
        // Add selectize values (columns and subqueries)
        $query = $this->addSelectizeValues($query);
        
        // Apply search
        if (!empty($search)) {
            $query->where('runtemplate.name LIKE ?', '%'.$search.'%');
        }
        
        // Apply ordering
        if (!empty($order)) {
            foreach ($order as $orderItem) {
                $columnIdx = (int) $orderItem['column'];
                $direction = $orderItem['dir'] === 'asc' ? 'ASC' : 'DESC';
                
                if (isset($columns[$columnIdx])) {
                    $columnName = $columns[$columnIdx]['data'];
                    
                    // Map to actual database columns
                    $orderColumn = match ($columnName) {
                        'id' => 'runtemplate.id',
                        'active' => 'runtemplate.active',
                        'interv' => 'runtemplate.interv',
                        'name' => 'runtemplate.name',
                        'executor' => 'runtemplate.executor',
                        default => null,
                    };
                    
                    if ($orderColumn) {
                        $query->orderBy($orderColumn.' '.$direction);
                    }
                }
            }
        } else {
            $query->orderBy('runtemplate.name ASC');
        }
        
        // Get total count before pagination using separate simple query to avoid JOIN issues
        $countQuery = $this->getFluentPDO()->from('runtemplate');
        
        if ($this->companyId !== null) {
            $countQuery->where('runtemplate.company_id', $this->companyId);
        }
        
        if ($this->appId !== null) {
            $countQuery->where('runtemplate.app_id', $this->appId);
        }
        
        if (!empty($search)) {
            $countQuery->where('runtemplate.name LIKE ?', '%'.$search.'%');
        }
        
        $recordsTotal = (int) $countQuery->count();
        $recordsFiltered = $recordsTotal;
        
        // Apply pagination
        $query->limit($length)->offset($start);
        
        // Execute query directly to avoid FluentPDO's automatic JOIN attempts
        // Use fetchAll() instead of iteration to prevent automatic relationship detection
        try {
            $results = $query->fetchAll();
        } catch (\PDOException $e) {
            // If there's still a JOIN issue, log the actual SQL for debugging
            error_log('CompanyAppRunTemplateLister SQL error: ' . $e->getMessage());
            error_log('Query: ' . $query->getQuery());
            throw $e;
        }
        
        // Format data
        $data = [];
        foreach ($results as $row) {
            $data[] = $this->completeDataRow($row);
        }
        
        return [
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ];
    }

    public function addSelectizeValues($query)
    {
        // Build WHERE clause for subqueries with company and app filters
        $jobWhereConditions = ['job.runtemplate_id = runtemplate.id'];
        
        if ($this->companyId !== null) {
            $jobWhereConditions[] = 'job.company_id = ' . (int) $this->companyId;
        }
        
        if ($this->appId !== null) {
            $jobWhereConditions[] = 'job.app_id = ' . (int) $this->appId;
        }
        
        $jobWhere = implode(' AND ', $jobWhereConditions);
        
        // Add subquery for last job with proper filters
        $query->select([
            'runtemplate.id',
            'runtemplate.active',
            'runtemplate.interv',
            'runtemplate.name',
            'runtemplate.success',
            'runtemplate.fail',
            'runtemplate.executor',
            "(SELECT job.id FROM job WHERE {$jobWhere} ORDER BY job.id DESC LIMIT 1) AS last_job_id",
            "(SELECT job.exitcode FROM job WHERE {$jobWhere} ORDER BY job.id DESC LIMIT 1) AS last_job_exitcode",
        ], true);

        // Don't call parent - we've already defined all columns we need
        return $query;
    }

    public function completeDataRow(array $dataRowRaw): array
    {
        // Preserve numeric ID for JavaScript row selection and other uses
        $numericId = $dataRowRaw['id'];
        
        // Format ID
        $dataRowRaw['id'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$numericId, '⚗️ #'.$numericId);

        // Format interval with emoji and name
        $dataRowRaw['interv'] = '<span title=\"'._(self::codeToInterval($dataRowRaw['interv'])).'\">'.self::getIntervalEmoji($dataRowRaw['interv']).' '._(self::codeToInterval($dataRowRaw['interv'])).'</span>';

        // Format active status with launch button or disabled icon
        $dataRowRaw['active'] = (string) $dataRowRaw['active']
            ? '&nbsp;<a href="schedule.php?id='.$numericId.'&when=now&executor=Native" title="'._('Launch now').'"><span style="color: green; font-weight: xx-large;">▶</span></a> '
            : '<span style="color: lightgray; font-weight: xx-large;" title="'._('Disabled').'">🚧</span>';

        // Format name as link
        $dataRowRaw['name'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$numericId, '<strong>'.$dataRowRaw['name'].'</strong>');

        // Format last job exit code
        if (!empty($dataRowRaw['last_job_id'])) {
            $exitCodeWidget = new Ui\ExitCode($dataRowRaw['last_job_exitcode']);
            $dataRowRaw['last_job'] = (string) new \Ease\Html\ATag(
                'job.php?id='.$dataRowRaw['last_job_id'],
                '🏁 #'.$dataRowRaw['last_job_id'].' '.$exitCodeWidget
            );
        } else {
            $dataRowRaw['last_job'] = '<span style="color: #999; font-style: italic;">'._('No jobs yet').'</span>';
        }

        // Format action icons
        $successIcons = self::actionIcons($dataRowRaw['success'] ? unserialize($dataRowRaw['success']) : null, ['style' => 'border-bottom: 4px solid green;']);
        $failIcons = self::actionIcons($dataRowRaw['fail'] ? unserialize($dataRowRaw['fail']) : null, ['style' => 'border-bottom: 4px solid red;']);

        $dataRowRaw['actions'] = (string) new \Ease\Html\ATag(
            'actions.php?id='.$numericId.'#SuccessActions',
            $successIcons
        ).' '.(string) new \Ease\Html\ATag(
            'actions.php?id='.$numericId.'#FailActions',
            $failIcons
        );

        // Format executor
        $dataRowRaw['executor'] = (string) new Ui\ExecutorImage($dataRowRaw['executor'], ['style' => 'height: 30px;']);

        return $dataRowRaw;
    }
}
