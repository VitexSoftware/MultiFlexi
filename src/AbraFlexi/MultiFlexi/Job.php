<?php

/**
 * Multi Flexi - Job Eengine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use \Zarplata\Zabbix\Request\Packet as ZabbixPacket;
use \Zarplata\Zabbix\Request\Metric as ZabbixMetric;

/**
 * Description of Job
 *
 * @author vitex
 */
class Job extends Engine
{

    public $myTable = 'job';

    /**
     * 
     * @var ZabbixSender
     */
    public $zabbixSender = null;

    /**
     * Environment for Current Job 
     * @var array
     */
    private $environment;

    /**
     * 
     * @var array
     */
    private $zabbixMessageData;

    /**
     * 
     * @var type
     */
    public $application = null;

    /**
     * 
     * @var type
     */
    public $company = null;

    /**
     * Job Object
     * 
     * @param int $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        parent::__construct($identifier, $options);
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->zabbixSender = new ZabbixSender(\Ease\Functions::cfg('ZABBIX_SERVER'));
        }
    }

    /**
     * Create New Job Record in database
     * 
     * @param int $companyId
     * @param int $appId
     * @param array $environment
     * 
     * @return int new job ID
     */
    public function newJob($companyId, $appId, $environment)
    {
        return $this->insertToSQL([
                    'company_id' => $companyId,
                    'app_id' => $appId,
                    'env' => \serialize($environment),
                    'exitcode' => -1,
                    'launched_by' => \Ease\Shared::user()->getMyKey()
        ]);
    }

    /**
     * Begin the Job
     *
     * @param int $appId
     * @param int $companyId
     *
     * @return int Job ID
     */
    public function runBegin($appId, $companyId, $environment = [])
    {

        $appId = $this->getDataValue('app_id');
        $companyId = $this->getDataValue('company_id');
        $this->setObjectName();
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany($companyId);
        $sqlLogger->setApplication($appId);
        $this->addStatusMessage('JOB: ' . $jobId . ' ' . json_encode($environment), 'debug');
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix(['phase' => 'jobStart', 'begin' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }
        if ($this->isProvisioned($appId, $companyId) == 0) {
            $this->addStatusMessage(_('Perform initial setup'), 'warning');
            $app = new Application((int) $appId);
            $setupCommand = $app->getDataValue('setup');
            if (!empty(trim($setupCommand))) {
                $app->addStatusMessage(_('Setup command') . ': ' . $setupCommand, 'debug');
                $appCompany = new AppToCompany();
                $appCompany->setMyKey($appCompany->appCompanyID($appId, $companyId));
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
                if ($result == 0) {
                    $appCompany->setProvision(1);
                    if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
                        $this->reportToZabbix([]);
                    }
                    $this->addStatusMessage('provision done', 'success');
                }
            }
        }

        return $jobId;
    }

    /**
     * Action at Job run finish
     * 
     * @param int $statusCode
     * @param string $stdout Job Output
     * @param string $stderr Job error output
     * 
     * @return int
     */
    public function runEnd($statusCode, $stdout, $stderr)
    {
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany(0);
        $sqlLogger->setApplication(0);
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix(['phase' => 'jobDone', 'stdout' => $stdout, 'stderr' => $stderr, 'exitcode' => $statusCode, 'end' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }
        return $this->updateToSQL(['end' => new \Envms\FluentPDO\Literal('NOW()'),
                    'stdout' => addslashes($stdout),
                    'stderr' => addslashes($stderr),
                    'exitcode' => $statusCode], ['id' => $this->getMyKey()]);
    }

    /**
     * Check App Provisionning state 
     * 
     * @param int $appId
     * @param int $companyId
     * 
     * @return boolean|null application with setup command provision state or setup command is not set
     */
    public function isProvisioned($appId, $companyId)
    {
        $appCompany = new AppToCompany();
        $appCompany->setMyKey($appCompany->appCompanyID($appId, $companyId));
        $appInfo = $appCompany->getAppInfo();
        return $appInfo['prepared'];
    }

    /**
     * @link https://datatables.net/examples/advanced_init/column_render.html 
     * 
     * @return string Column rendering
     */
    public function columnDefs()
    {
        return '
"columnDefs": [
           // { "visible": false,  "targets": [ 0 ] }
        ]            
,
';
    }

    /**
     * 
     * @param int $companyAppId
     */
    public function prepareJob($companyAppId)
    {
        $companyApp = new AppToCompany($companyAppId);
        $this->environment = $companyApp->getAppEnvironment();
        $this->application = new Application($companyApp->getDataValue('app_id'));
        $this->company = new Company($companyApp->getDataValue('company_id'));
        $this->loadFromSQL($this->newJob($companyApp->getDataValue('company_id'), $companyApp->getDataValue('app_id'), $this->environment));
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->zabbixMessageData = [
                'phase' => 'prepared',
                'job_id' => $this->getMyKey(),
                'app_id' => $companyApp->getDataValue('app_id'),
                'app_name' => $this->application->getDataValue('nazev'),
                'begin' => null,
                'done' => null,
                'company_id' => $companyApp->getDataValue('company_id'),
                'company_name' => $this->company->getDataValue('nazev'),
                'exitcode' => null,
                'stdout' => null,
                'stderr' => null,
                'launched_by' => \Ease\Shared::user()->getMyKey()
            ];
        }
    }

    /**
     * Send Job phse Message to zabbix
     * 
     * @param array $messageData override fields
     */
    public function reportToZabbix($messageData)
    {
        $packet = new ZabbixPacket();
        $me = \Ease\Functions::cfg('ZABBIX_SOURCE');
        $this->zabbixMessageData = array_merge($this->zabbixMessageData, $messageData);
        $packet->addMetric((new ZabbixMetric('multiflexi.job', json_encode($this->zabbixMessageData)))->withHostname($me));
        $this->zabbixSender->send($packet);
        $this->addStatusMessage($me . ': Job phase ' . $this->zabbixMessageData['phase'] . ' reported to zabbix ' . \Ease\Functions::cfg('ZABBIX_SERVER'), 'debug');
    }

    public function performJob($appCompanyId)
    {
        $this->prepareJob($appCompanyId);
        LogToSQL::singleton()->setApplication($this->application->getMyKey());
        $cmdparams = $this->application->getDataValue('cmdparams');
        foreach ($this->environment as $envKey => $envValue) {
            $this->addStatusMessage(sprintf(_('Setting custom Environment: export %s=%s'), $envKey, $envValue), 'debug');
            $cmdparams = str_replace('{' . $envKey . '}', $envValue, $cmdparams);
        }

        $exec = $this->application->getDataValue('executable');
        $this->addStatusMessage('command begin: ' . $exec . ' ' . $cmdparams . '@' . $this->company->getDataValue('nazev'));
        $process = new \Symfony\Component\Process\Process(array_merge([$exec], explode(' ', $cmdparams)), null, $this->environment, null, 32767);
        $process->run(function ($type, $buffer) {
            $logger = new \Ease\Sand();
            $logger->setObjectName('Runner');
            if (\Symfony\Component\Process\Process::ERR === $type) {
                $logger->addStatusMessage($buffer, 'error');
            } else {
                $logger->addStatusMessage($buffer, 'success');
            }
        });
        $this->addStatusMessage('end' . $exec . '@' . $this->application->getDataValue('name'));
        $this->runEnd($process->getExitCode(), $process->getOutput(), $process->getErrorOutput());
    }
}
