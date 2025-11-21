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
        
        // Apply filters immediately
        if ($this->companyId !== null) {
            $query->where('runtemplate.company_id', $this->companyId);
        }

        if ($this->appId !== null) {
            $query->where('runtemplate.app_id', $this->appId);
        }
        
        return $query;
    }

    public function addSelectizeValues($query)
    {
        // Add subquery for last job
        $query->select([
            'runtemplate.id',
            'runtemplate.active',
            'runtemplate.interv',
            'runtemplate.name',
            'runtemplate.success',
            'runtemplate.fail',
            'runtemplate.executor',
            '(SELECT job.id FROM job WHERE job.runtemplate_id = runtemplate.id ORDER BY job.id DESC LIMIT 1) AS last_job_id',
            '(SELECT job.exitcode FROM job WHERE job.runtemplate_id = runtemplate.id ORDER BY job.id DESC LIMIT 1) AS last_job_exitcode',
        ], true);

        // Don't call parent - we've already defined all columns we need
        return $query;
    }

    public function completeDataRow(array $dataRowRaw): array
    {
        // Format ID
        $dataRowRaw['id'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '⚗️ #'.$dataRowRaw['id']);

        // Format interval with emoji and name
        $dataRowRaw['interv'] = '<span title=\"'._(self::codeToInterval($dataRowRaw['interv'])).'\">'.self::getIntervalEmoji($dataRowRaw['interv']).' '._(self::codeToInterval($dataRowRaw['interv'])).'</span>';

        // Format active status with launch button or disabled icon
        $dataRowRaw['active'] = (string) $dataRowRaw['active']
            ? '&nbsp;<a href="schedule.php?id='.$dataRowRaw['id'].'&when=now&executor=Native" title="'._('Launch now').'"><span style="color: green; font-weight: xx-large;">▶</span></a> '
            : '<span style="color: lightgray; font-weight: xx-large;" title="'._('Disabled').'">🚧</span>';

        // Format name as link
        $dataRowRaw['name'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '<strong>'.$dataRowRaw['name'].'</strong>');

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
            'actions.php?id='.$dataRowRaw['id'].'#SuccessActions',
            $successIcons
        ).' '.(string) new \Ease\Html\ATag(
            'actions.php?id='.$dataRowRaw['id'].'#FailActions',
            $failIcons
        );

        // Format executor
        $dataRowRaw['executor'] = (string) new Ui\ExecutorImage($dataRowRaw['executor'], ['style' => 'height: 30px;']);

        return $dataRowRaw;
    }
}
