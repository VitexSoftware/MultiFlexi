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
 * Description of Scheduler.
 *
 * @author vitex
 */
class ScheduleLister extends DBEngine
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'schedule';
        $this->nameColumn = '';
        parent::__construct($identifier, $options);
    }

    /**
     * @see https://datatables.net/examples/advanced_init/column_render.html
     *
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return <<<'EOD'

"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]
,

EOD;
    }

    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID')],
            ['name' => 'after', 'type' => 'text', 'label' => _('After')],
            ['name' => 'job', 'type' => 'text', 'label' => _('Job')],
            ['name' => 'app_name', 'type' => 'text', 'label' => _('App')],
            ['name' => 'runtemplate_name', 'type' => 'text', 'label' => _('Runtemplate')],
            ['name' => 'company_name', 'type' => 'text', 'label' => _('Company')],
        ]);
    }
    public function completeDataRow(array $dataRowRaw): array
    {
        $dataRow['id'] = $dataRowRaw['id'];
        $dataRow['after'] = $dataRowRaw['after'];
        $dataRow['job'] = (string) new \Ease\Html\ATag('job.php?id='.$dataRowRaw['job'], '🏁&nbsp;'.$dataRowRaw['job']);
        $dataRow['app_name'] = (string) new \Ease\Html\ATag('app.php?id='.$dataRowRaw['app_id'], '🧩&nbsp;'.$dataRowRaw['app_name']);
        $dataRow['runtemplate_name'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['runtemplate_id'], '⚗️&nbsp;'.$dataRowRaw['runtemplate_name']);
        $dataRow['company_name'] = (string) new \Ease\Html\ATag('company.php?id='.$dataRowRaw['company_id'], '🏭&nbsp;'.$dataRowRaw['company_name']);

        return $dataRow;
    }

    public function listingQuery(): \Envms\FluentPDO\Queries\Select
    {
        return parent::listingQuery()
            ->leftJoin('job ON job.id = schedule.job')
            ->leftJoin('user ON user.id = job.launched_by')
            ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')->select(['runtemplate.name AS runtemplate_name', 'runtemplate.id AS runtemplate_id'])
            ->leftJoin('apps ON apps.id = runtemplate.app_id')->select(['apps.name AS app_name', 'apps.id AS app_id'])
            ->leftJoin('company ON company.id = runtemplate.company_id')->select(['company.name AS company_name', 'company.id AS company_id']);
    }
}
