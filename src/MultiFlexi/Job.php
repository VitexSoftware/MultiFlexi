<?php

/**
 * Multi Flexi - Job Eengine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

use \AbraFlexi\MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use \AbraFlexi\MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;

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
    private $zabbixMessageData = [];

    /**
     * 
     * @var App
     */
    public $application = null;

    /**
     * 
     * @var Company
     */
    public $company = null;

    /**
     * @var array
     */
    public $outputCache = [];

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
        if (is_null($this->getDataValue('company_id')) === false) {
            $this->company = new Company(intval($this->getDataValue('company_id')));
        }
        if (is_null($this->getDataValue('app_id')) === false) {
            $this->application = new Application(intval($this->getDataValue('app_id')));
        }
        $this->setObjectName();
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
     * @return int Job ID
     */
    public function runBegin()
    {
        $appId = $this->application->getMyKey();
        $companyId = $this->company->getMyKey();
        $this->setObjectName();
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany($companyId);
        $sqlLogger->setApplication($appId);
        $jobId = $this->getMyKey();
        $this->addStatusMessage('JOB: ' . $jobId . ' ' . json_encode($this->environment), 'debug');
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix(['phase' => 'jobStart', 'begin' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }
        if ($this->isProvisioned($appId, $companyId) == 0) {
            $this->addStatusMessage(_('Perform initial setup'), 'warning');
            $app = new Application((int) $appId);
            $setupCommand = $app->getDataValue('setup');
            if (!empty(trim($setupCommand))) {
                $app->addStatusMessage(_('Setup command') . ': ' . $setupCommand, 'debug');
                $appCompany = new RunTemplate();
                $appCompany->setMyKey($appCompany->runTemplateID($appId, $companyId));
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
                        $this->reportToZabbix(['phase' => 'setup']); //TODO: report provision done
                    }
                    $this->addStatusMessage('provision done', 'success');
                }
            }
        }
        $this->updateToSQL(['id'=> $this->getMyKey(), 'begin' => new \Envms\FluentPDO\Literal('NOW()')]);
        return $jobId;
    }

    /**
     * Action at Job run finish
     * 
     * @param int    $statusCode
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
        return $this->updateToSQL([
                    'end' => new \Envms\FluentPDO\Literal('NOW()'),
                    'stdout' => addslashes($stdout),
                    'stderr' => addslashes($stderr),
                    'exitcode' => $statusCode
                        ], ['id' => $this->getMyKey()]);
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
        $appCompany = new RunTemplate();
        $appCompany->setMyKey($appCompany->runTemplateID($appId, $companyId));
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
     * Prepare Job for run
     * 
     * @param int   $runTemplateId
     * @param array $envOverride use to change default env
     */
    public function prepareJob(int $runTemplateId, $envOverride = [])
    {
        $companyApp = new RunTemplate($runTemplateId);
        $this->environment = array_merge($companyApp->getAppEnvironment(), $envOverride);
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
                'end' => null,
                'company_id' => $companyApp->getDataValue('company_id'),
                'company_name' => $this->company->getDataValue('nazev'),
                'exitcode' => -1,
                'stdout' => null,
                'stderr' => null,
                'launched_by_id' => intval(\Ease\Shared::user()->getMyKey()),
                'launched_by' => empty(\Ease\Shared::user()->getUserLogin()) ? 'cron' : \Ease\Shared::user()->getUserLogin()
            ];
        }
    }

    /**
     * Schedule job execution
     * 
     * @param \DateTime $when
     * 
     * @return int schedule ID
     */
    public function scheduleJobRun($when)
    {
        $scheduler = new Scheduler();
        return $scheduler->addJob($this, $when);
    }

    /**
     * Send Job phase Message to zabbix
     * 
     * @param array $messageData override fields
     */
    public function reportToZabbix($messageData)
    {
        $packet = new ZabbixPacket();
        $me = \Ease\Functions::cfg('ZABBIX_HOST');
        $this->zabbixMessageData = array_merge($this->zabbixMessageData, $messageData);
        $packet->addMetric((new ZabbixMetric('multiflexi.job', json_encode($this->zabbixMessageData)))->withHostname($me));
        $this->zabbixSender->send($packet);
    }

    /**
     * Perform Job
     */
    public function performJob()
    {
        $this->runBegin();
        LogToSQL::singleton()->setApplication($this->application->getMyKey());
        $exec = $this->application->getDataValue('executable');
        $cmdparams = $this->getCmdParams();
        $this->addStatusMessage('command begin: ' . $exec . ' ' . $cmdparams . '@' . $this->company->getDataValue('nazev'));
        $process = new \Symfony\Component\Process\Process(array_merge([$exec], explode(' ', $cmdparams)), null, $this->environment, null, 32767);
        $process->run(function ($type, $buffer) {
            $logger = new \Ease\Sand();
            $logger->setObjectName('Runner');
            if (\Symfony\Component\Process\Process::ERR === $type) {
                $logger->addStatusMessage($buffer, 'error');
                $this->addOutput($buffer, 'error');
            } else {
                $logger->addStatusMessage($buffer, 'success');
                $this->addOutput($buffer, 'success');
            }
        });
        $this->addStatusMessage('end' . $exec . '@' . $this->application->getDataValue('name'));
        $this->runEnd($process->getExitCode(), $process->getOutput(), $process->getErrorOutput());
    }

    /**
     * Add Output line into cache
     */
    public function addOutput($line, $type)
    {
        $this->outputCache[microtime()] = ['line' => $line, 'type' => $type];
    }

    /**
     * Get Output cache as plaintext
     */
    public function getOutputCachePlaintext()
    {
        $output = '';
        foreach ($this->outputCache as $line) {
            $output .= $line['line'] . "\n";
        }
        return $output;
    }

    /**
     * Obtain Full Job Command Line
     * 
     * @return string  command line
     */
    public function getCmdline()
    {
        return $this->application->getDataValue('executable') . ' ' . $this->getCmdParams();
    }

    /**
     * Obtain Job Command Line Parameters
     * 
     * @return string  command line parameters
     */
    public function getCmdParams()
    {
        $cmdparams = $this->application->getDataValue('cmdparams');
        foreach ($this->environment as $envKey => $envValue) {
            $this->addStatusMessage(sprintf(_('Setting custom Environment: export %s=%s'), $envKey, $envValue), 'debug');
            $cmdparams = str_replace('{' . $envKey . '}', $envValue, strval($cmdparams));
        }
        return $cmdparams;
    }

    /**
     * Obtain Job Output
     * 
     * @return string job output
     */
    public function getOutput()
    {
        return $this->getDataValue('stdout');
    }

    public function cleanUp()
    {
        // TODO: Delete Uploaded files if any
    }
}