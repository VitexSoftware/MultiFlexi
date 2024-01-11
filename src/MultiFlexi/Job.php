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
    /**
     *
     * @var executor
     */
    public $executor;

    /**
     *
     * @var string
     */
    public $myTable = 'job';

    /**
     *
     * @var array
     */
    public static $intervalCode = [
        'i' => 'minutly',
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
     *
     * @var RunTemplate
     */
    public $runTemplate;

    /**
     * Job Object
     *
     * @param int $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        parent::__construct($identifier, $options);
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->zabbixSender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
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
     * @param string $executor    Chosen Executor class name
     *
     * @return int new job ID
     */
    public function newJob(int $companyId, int $appId, array $environment, string $scheduled = 'adhoc', $executor = 'Native')
    {
        $jobId = $this->insertToSQL([
            'company_id' => $companyId,
            'app_id' => $appId,
            'env' => \serialize($environment),
            'exitcode' => -1,
            'stdout' => '',
            'stderr' => '',
            'schedule' => $scheduled,
            'executor' => $executor,
            'launched_by' => \Ease\Shared::user()->getMyKey()
        ]);
        $environment['JOB_ID']['value'] = $jobId;
        $this->environment = $environment;
        $this->updateToSQL(['env' => serialize($environment)], ['id' => $jobId]);
        return $jobId;
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
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
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
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix(['phase' => 'jobDone', 'stdout' => $stdout, 'stderr' => $stderr, 'exitcode' => $statusCode, 'end' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }

        $this->setData([
            'stdout' => addslashes($stdout),
            'stderr' => addslashes($stderr),
            'command' => $this->executor->commandline(),
            'exitcode' => $statusCode
        ]);

        $this->performActions($statusCode == 0 ? 'success' : 'fail');

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
     * @param array  $envOverride   use to change default env [env with info]
     * @param string $scheduled     Time to launch
     * @param string $executor      Executor Class Name
     */
    public function prepareJob(int $runTemplateId, $envOverride = [], $scheduled = 'adhoc', $executor = 'Native')
    {
        $this->runTemplate = new RunTemplate($runTemplateId);
        $appId = $this->runTemplate->getDataValue('app_id');
        $companyId = $this->runTemplate->getDataValue('company_id');

        $this->application = new Application($appId);
        LogToSQL::singleton()->setApplication($appId);

        $this->company = new Company($companyId);

        $this->environment = array_merge($this->getFullEnvironment(), $envOverride);
        $this->loadFromSQL($this->newJob($companyId, $appId, $this->environment, $scheduled, $executor));
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->zabbixMessageData = [
                'phase' => 'prepared',
                'job_id' => $this->getMyKey(),
                'app_id' => $appId,
                'app_name' => $this->application->getDataValue('name'),
                'begin' => null,
                'end' => null,
                'company_id' => $companyId,
                'company_name' => $this->company->getDataValue('name'),
                'company_code' => $this->company->getDataValue('code'),
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
                $appInfo = $this->runTemplate->getAppInfo();
                $appEnvironment = Environmentor::flatEnv($this->environment);
                $process = new \Symfony\Component\Process\Process(
                    explode(' ', $setupCommand),
                    null,
                    $appEnvironment,
                    null,
                    32767
                );
                $result = $process->run(function ($type, $buffer) {
                    $logger = new Runner();
                    if ($buffer) {
                        if (\Symfony\Component\Process\Process::ERR === $type) {
                            $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                            $logger->addStatusMessage($buffer, 'error');
                        } else {
                            $logger->addStatusMessage($buffer, 'success');
                            $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                        }
                        echo new \Ease\Html\DivTag(nl2br($outline));
                    }
                });
                if ($result == 0) {
                    $this->runTemplate->setProvision(1);
                    if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
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
        $hostname = \Ease\Shared::cfg('ZABBIX_HOST');
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
        $this->executor->launch();
        $this->runEnd($this->executor->getExitCode(), $this->executor->getOutput(), $this->executor->getErrorOutput());
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
            foreach ($this->environment as $envKey => $envInfo) {
                $cmdparams = str_replace('{' . $envKey . '}', $envInfo['value'], strval($cmdparams));
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
        foreach ($environment as $key => $envInfo) {
            $launcher[] = "";
            $launcher[] = '# Source ' . $envInfo['source'];
            $launcher[] = 'export ' . $key . "='" . $envInfo['value'] . "'";
            if (array_key_exists('description', $envInfo)) {
                $launcher[] = '# ' . $envInfo['description'];
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
     * Get Interval code by Name
     *
     * @param string $interval
     *
     * @return string
     */
    public static function intervalToCode($interval)
    {
        return array_key_exists($interval, array_flip(self::$intervalCode)) ? array_flip(self::$intervalCode)[$interval] : 'n/a';
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
     * Gives Full Environment with Full info
     *
     * @return array Environment with metadata
     */
    public function getFullEnvironment()
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');
        $jobEnv = [];
        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\' . $injector;
            $jobEnv = array_merge($jobEnv, (new $injectorClass($this))->getEnvironment());
        }
        return $jobEnv;
    }

    /**
     * Generate Environment for current Job
     *
     * @return array
     */
    public function compileEnv()
    {
        return Environmentor::flatEnv($this->getFullEnvironment());
    }

    /**
     * @inheritDoc
     */
    public function takeData($data)
    {
        parent::takeData($data);

        $this->environment = empty($this->getDataValue('env')) ? [] : unserialize($this->getDataValue('env'));
        if (is_null($this->getDataValue('company_id')) === false) {
            $this->company = new Company(intval($this->getDataValue('company_id')));
        }
        if (is_null($this->getDataValue('app_id')) === false) {
            $this->application = new Application(intval($this->getDataValue('app_id')));
        }

        if ($this->getDataValue('executor')) {
            $executorClass = '\\MultiFlexi\\Executor\\' . $this->getDataValue('executor');
            if (class_exists($executorClass)) {
                $this->executor = new $executorClass($this);
            } else {
                $this->addStatusMessage(sprintf(_('Requested Executor %s not availble'), $executorClass), 'warning');
                $this->executor = new \MultiFlexi\Executor\Native($this);
            }
        }
    }

    /**
     * export .env file content
     *
     * @return string
     */
    public function envFile()
    {
        $launcher[] = '# ' . \Ease\Shared::appName() . ' v' . \Ease\Shared::AppVersion() . ' job #' . $this->getMyKey() . ' environment. Generated ' . (new \DateTime())->format('Y-m-d H:i:s') . ' for company: ' . $this->company->getDataValue('name');
        $launcher[] = '';
        $environment = $this->getDataValue('env') ? unserialize($this->getDataValue('env')) : [];
        foreach ($environment as $key => $envInfo) {
            $launcher[] = $key . "='" . $envInfo['value'] . "'";
        }
        return implode("\n", $launcher);
    }

    /**
     *
     * @param string success | fail
     */
    public function performActions($mode)
    {
        $actions = $this->runTemplate->getDataValue($mode) ? unserialize($this->runTemplate->getDataValue($mode)) : [];
        $modConf = new ModConfig();
        foreach ($actions as $action => $enabled) {
            $actionClass = '\\MultiFlexi\\Action\\' . $action;
            if ($enabled && class_exists($actionClass)) {
                $actionHandler = new $actionClass($this);
                $actionHandler->setData($modConf->getModuleConf($action));
                $actionHandler->perform();
            }
        }
    }
}
