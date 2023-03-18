<?php

/**
 * Multi Flexi - Job Eengine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

/**
 * Description of Job
 *
 * @author vitex
 */
class Job extends Engine {

    public $myTable = 'job';

    /**
     * Begin the Job
     *
     * @param int $appId
     * @param int $companyId
     *
     * @return int Job ID
     */
    public function runBegin($appId, $companyId) {
        $jobId = $this->insertToSQL(['company_id' => $companyId, 'app_id' => $appId,
            'exitcode' => 255]);
        $this->setMyKey($jobId);
        $this->setObjectName();
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany($companyId);
        $sqlLogger->setApplication($appId);

        $this->addStatusMessage('JOB: ' . $jobId, 'debug');
        if ($this->isProvisioned($appId, $companyId) == 0) {
            $this->addStatusMessage(_('Perform initial setup'), 'warning');
            $app = new Application((int) $appId);
            $setupCommand = $app->getDataValue('setup');
            
            $appCompany = new AppToCompany();
            $appCompany->setMyKey($appCompany->appCompanyID( $appId,$companyId));
            $appInfo = $appCompany->getAppInfo();
            $appEnvironment = $appCompany->getAppEnvironment();
            $process = new \Symfony\Component\Process\Process(explode(' ', $setupCommand), null, $appEnvironment, null, 32767);
            
            $result = $process->run(function ($type, $buffer) {
                $logger = new Runner();
                if (\Symfony\Component\Process\Process::ERR === $type) {
                    $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                    $logger->addStatusMessage($buffer, 'error');
                } else {
                    $logger->addStatusMessage($buffer, 'success');
                    $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                }
                echo new \Ease\Html\DivTag(nl2br($outline));
            });
            if($result == 0){
                $appCompany->setProvision(1);
                $this->addStatusMessage('provision done', 'success');
            }
            
        }

        return $jobId;
    }

    /**
     * Action at Job run finish
     * 
     * @param int $runId
     * @param int $statusCode
     * 
     * @return int
     */
    public function runEnd($runId, $statusCode) {
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany(0);
        $sqlLogger->setApplication(0);
        return $this->updateToSQL(['end' => new \Envms\FluentPDO\Literal('NOW()'),
                    'exitcode' => $statusCode], ['id' => $runId]);
    }

    /**
     * Check App Provisionning state 
     * 
     * @param int $appId
     * @param int $companyId
     * 
     * @return boolean|null application with setup command provision state or setup command is not set
     */
    public function isProvisioned($appId, $companyId) {
        $appCompany = new AppToCompany();
        $appCompany->setMyKey($appCompany->appCompanyID( $appId,$companyId));
        $appInfo = $appCompany->getAppInfo();
        return $appInfo['prepared'];
    }

}
