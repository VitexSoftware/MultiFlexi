<?php

/**
 * Multi Flexi - Job Eengine
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

use MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;
use MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;

/**
 * Description of Job
 *
 * @author vitex
 */
class Job extends Engine
{
    public $myTable = 'job';
    public static $intervalCode = [
        'i' => 'instant',
        'n' => 'disabled',
        'y' => 'yearly',
        'h' => 'hourly',
        'm' => 'monthly',
        'w' => 'weekly',
        'd' => 'daily'
    ];

    /**
     *
     * @var ZabbixSender
     */
    public $zabbixSender = null;

    /**
     * Environment for Current Job
     * @var array
     */
    private $environment = [];

    /**
     * Executed commandline
     * @var string
     */
    private $commandline;

    /**
     *
     * @var array
     */
    private $zabbixMessageData = [];

    /**
     *
     * @var Application
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
     * @param int    $companyId   Job is performed for Company with given ID
     * @param int    $appId       Job is based on this Application
     * @param array  $environment Environmet prepared for Job execution
     * @param string $scheduled   Schedule Info
     *
     * @return int new job ID
     */
    public function newJob(int $companyId, int $appId, array $environment, string $scheduled = 'adhoc')
    {
        return $this->insertToSQL([
                    'company_id' => $companyId,
                    'app_id' => $appId,
                    'env' => \serialize($environment),
                    'exitcode' => -1,
                    'stdout' => '',
                    'stderr' => '',
                    'schedule' => $scheduled,
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
        //$this->addStatusMessage('JOB: ' . $jobId . ' ' . json_encode($this->environment), 'debug');
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix(['phase' => 'jobStart', 'begin' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }
        $this->updateToSQL(['id' => $this->getMyKey(), 'begin' => new \Envms\FluentPDO\Literal('NOW()')]);
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
                    'command' => $this->commandline,
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
     * @param int    $runTemplateId ID of RunTempate to use
     * @param array  $envOverride   use to change default env
     * @param string $name          Description
     */
    public function prepareJob(int $runTemplateId, $envOverride = [], $scheduled = 'adhoc')
    {
        $runTemplate = new RunTemplate($runTemplateId);
        $appId = $runTemplate->getDataValue('app_id');
        $companyId = $runTemplate->getDataValue('company_id');

        $this->application = new Application($appId);
        $this->company = new Company($companyId);

        $this->environment = array_merge($this->compileEnv(), $envOverride);
        $this->loadFromSQL($this->newJob($companyId, $appId, $this->environment, $scheduled));
        if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
            $this->zabbixMessageData = [
                'phase' => 'prepared',
                'job_id' => $this->getMyKey(),
                'app_id' => $appId,
                'app_name' => $this->application->getDataValue('name'),
                'begin' => null,
                'end' => null,
                'company_id' => $companyId,
                'company_name' => $this->company->getDataValue('name'),
                'exitcode' => -1,
                'stdout' => null,
                'stderr' => null,
                'launched_by_id' => intval(\Ease\Shared::user()->getMyKey()),
                'launched_by' => empty(\Ease\Shared::user()->getUserLogin()) ? 'cron' :
                \Ease\Shared::user()->getUserLogin()
            ];
        }

        $setupCommand = $this->application->getDataValue('setup');
        if ($setupCommand && $this->isProvisioned($appId, $companyId) == 0) {
            $this->addStatusMessage(_('Perform initial setup'), 'warning');
            if (!empty(trim($setupCommand))) {
                $this->application->addStatusMessage(_('Setup command') . ': ' . $setupCommand, 'debug');
                $appInfo = $runTemplate->getAppInfo();
                $appEnvironment = $runTemplate->getAppEnvironment();
                $process = new \Symfony\Component\Process\Process(
                    explode(' ', $setupCommand),
                    null,
                    $appEnvironment,
                    null,
                    32767
                );
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
        $hostname = \Ease\Functions::cfg('ZABBIX_HOST');
        $this->zabbixMessageData = array_merge($this->zabbixMessageData, $messageData);
        $packet->addMetric((new ZabbixMetric('multiflexi.job', json_encode($this->zabbixMessageData)))->withHostname($hostname));
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
        $this->commandline = $exec . ' ' . $cmdparams;
        $this->setDataValue('commandline', $this->commandline);
        $this->addStatusMessage('command begin: ' . $this->commandline . '@' . $this->company->getDataValue('name'));
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
        if (is_array($this->environment)) {
            foreach ($this->environment as $envKey => $envValue) {
                $cmdparams = str_replace('{' . $envKey . '}', $envValue, strval($cmdparams));
            }
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

    /**
     * #Generate Job Launcher
     *
     * @return string
     */
    public function launcherScript()
    {
        $launcher[] = '#!/bin/bash';
        $launcher[] = '';
        $launcher[] = '# ' . \Ease\Shared::appName() . ' v' . \Ease\Shared::AppVersion() . ' job #' . $this->getMyKey() . ' launcher. Generated ' . (new \DateTime())->format('Y-m-d H:i:s') . ' for company: ' . $this->company->getDataValue('name');
        $launcher[] = '';
        $environment = $this->getDataValue('env') ? unserialize($this->getDataValue('env')) : [];
        foreach ($environment as $key => $value) {
            if (is_string($value)) {
                $launcher[] = 'export ' . $key . "='" . $value . "'";
            }
        }
        $launcher[] = '';
        $launcher[] = $this->application->getDataValue('executable') . ' ' . $this->getCmdParams();
        return implode("\n", $launcher);
    }

    /**
     * Get Job Interval by Code
     *
     * @param string $code
     *
     * @return string
     */
    public static function codeToInterval($code)
    {
        return array_key_exists($code, self::$intervalCode) ? self::$intervalCode[$code] : 'n/a';
    }

    /**
     * Current Job Environment
     *
     * @return array
     */
    public function getEnv()
    {
        return $this->environment;
    }

    /**
     * Generate Environment for current Job
     *
     * @return array
     */
    public function compileEnv()
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');
        $jobEnv = [];
        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\' . $injector;
            $jobEnv = array_merge($jobEnv, (new $injectorClass($this))->getEnvironment());
        }
        return Environmentor::flatEnv($jobEnv);
    }

    /**
     * @inheritDoc
     */
    public function loadFromSQL($itemID)
    {
        $result = parent::loadFromSQL($itemID);
        $this->environment = empty($this->getDataValue('env')) ? [] : unserialize($this->getDataValue('env'));
        return $result;
    }

    public function envFile()
    {
        $launcher[] = '# ' . \Ease\Shared::appName() . ' v' . \Ease\Shared::AppVersion() . ' job #' . $this->getMyKey() . ' environment. Generated ' . (new \DateTime())->format('Y-m-d H:i:s') . ' for company: ' . $this->company->getDataValue('name');
        $launcher[] = '';
        $environment = $this->getDataValue('env') ? unserialize($this->getDataValue('env')) : [];
        foreach ($environment as $key => $value) {
            if (is_string($value)) {
                $launcher[] = $key . "='" . $value . "'";
            }
        }
        return implode("\n", $launcher);
    }
}
