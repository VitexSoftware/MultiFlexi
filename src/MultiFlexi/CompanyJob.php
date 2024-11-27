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

class CompanyJob extends DBEngine implements DatabaseEngine
{
    public $companyId;
    public $appId;
    public function __construct($init = null, $filter = [])
    {
        $this->myTable = 'job';
        parent::__construct($init, $filter);
    }

    /**
     * columns to be selected from database.
     *
     * @return array
     */
    public function getColumns()
    {
        return ['id', 'company_id', 'app_id', 'env', 'exitcode', 'launched_by', 'launched', 'finished', 'finished_by', 'status', 'status_message'];
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
            ['name' => 'id', 'type' => 'text', 'label' => _('Job ID'),
                'detailPage' => 'job.php',
                'valueColumn' => 'job.id',
                'idColumn' => 'job.id',
            ],
            ['name' => 'app_id', 'type' => 'selectize', 'label' => _('Application'),
                'listingPage' => 'apps.php',
                'detailPage' => 'app.php',
                'idColumn' => 'app',
                'valueColumn' => 'apps.name',
                'engine' => '\MultiFlexi\Application',
                'filterby' => 'name',
            ],
            ['name' => 'exitcode', 'type' => 'text', 'label' => _('Exit Code')],
            ['name' => 'stdout', 'type' => 'text', 'hidden' => true, 'label' => _('Standard Output')],
            ['name' => 'stderr', 'type' => 'text', 'hidden' => true, 'label' => _('Standard Error')],
            ['name' => 'begin', 'type' => 'datetime', 'label' => _('Job start time')],
            ['name' => 'end', 'type' => 'datetime', 'label' => _('Job Finish time')],
            ['name' => 'company_id', 'type' => 'selectize', 'label' => _('Company'),
                'listingPage' => 'companies.php',
                'detailPage' => 'company.php',
                'idColumn' => 'company',
                'valueColumn' => 'company.name',
                'engine' => '\MultiFlexi\Company',
                'filterby' => 'name',
            ],
            ['name' => 'launched_by', 'type' => 'selectize', 'label' => _('Launcher'),
                'listingPage' => 'users.php',
                'detailPage' => 'user.php',
                'idColumn' => 'user',
                'valueColumn' => 'user.login',
                'engine' => '\MultiFlexi\User',
                'filterby' => 'name',
            ],
        ]);
    }

    public function addSelectizeValues($query)
    {
        $query->leftJoin('apps ON apps.id = job.app_id')
            ->leftJoin('company ON company.id = job.company_id')
            ->leftJoin('user ON user.id = job.launched_by');

        return parent::addSelectizeValues($query);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        switch ($dataRowRaw['exitcode']) {
            case '0':
                $dataRowRaw['DT_RowClass'] = 'bg-success  text-white';

                break;
            case '1':
                $dataRowRaw['DT_RowClass'] = 'bg-warning  text-dark';

                break;
            case '255':
                $dataRowRaw['DT_RowClass'] = 'bg-danger  text-dark';

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

        //        $dataRowRaw['message'] = (new AnsiToHtmlConverter())->convert(str_replace('.........', '......... ', $dataRowRaw['message']));
        //        $dataRowRaw['created'] = (new LiveAge((new DateTime($dataRowRaw['created']))->getTimestamp()))->__toString();

        return parent::completeDataRow($dataRowRaw);
    }

    public function tableCode($tableId)
    {
        return <<<'EOD'

 "order": [[ 1, "asc" ]],

EOD;
    }

    public function setCompany($companyId): void
    {
        $this->companyId = $companyId;
        $this->filter['company_id'] = $companyId;
    }

    public function setApp($appId): void
    {
        $this->appId = $appId;
        $this->filter['app_id'] = $appId;
    }
}
