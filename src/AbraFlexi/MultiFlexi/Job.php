<?php
/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

/**
 * Description of Job
 *
 * @author vitex
 */
class Job extends Engine
{
    public $myTable = 'job';

    /**
     * Begin the Job
     *
     * @param int $app_id
     * @param int $companyId
     *
     * @return int Job ID
     */
    public function runBegin($app_id, $companyId)
    {
        $jobId = $this->insertToSQL(['company_id' => $companyId, 'app_id' => $app_id,
            'exitcode' => 255]);
        $this->setMyKey($jobId);
        $this->setObjectName();
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany($companyId);
        $sqlLogger->setApplication($app_id);
        $this->addStatusMessage('JOB: '.$jobId, 'debug');
        return $jobId;
    }

    /**
     *
     * @param int $runId
     * @param int $statusCode
     * 
     * @return type
     */
    public function runEnd($runId, $statusCode)
    {
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany(0);
        $sqlLogger->setApplication(0);
        return $this->updateToSQL(['end' => new \Envms\FluentPDO\Literal('NOW()'),
                'exitcode' => $statusCode], ['id' => $runId]);
    }
}