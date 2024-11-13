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

use MultiFlexi\Zabbix\Request\Metric as ZabbixMetric;
use MultiFlexi\Zabbix\Request\Packet as ZabbixPacket;

/**
 * Description of Job.
 *
 * @author vitex
 */
class Job extends Engine
{
    public executor $executor;
    public static array $intervalCode = [
        'y' => 'yearly',
        'm' => 'monthly',
        'w' => 'weekly',
        'd' => 'daily',
        'h' => 'hourly',
        'i' => 'minutly',
        'n' => 'disabled',
    ];
    public static array $intervalSecond = [
        'n' => '0',
        'i' => '60',
        'h' => '3600',
        'd' => '86400',
        'w' => '604800',
        'm' => '2629743',
        'y' => '31556926',
    ];
    public static $intervalZabbix = [
        'n' => '0',
        'i' => 'm0-59',
        'h' => 'h0-23',
        'd' => 'm0',
        'w' => 'wd1',
        'm' => 'md1',
        'y' => '31556926',
    ];
    public ?ZabbixSender $zabbixSender = null;
    public ?Application $application = null;
    public ?Company $company = null;
    public ?RunTemplate $runTemplate;

    /**
     * Environment for Current Job.
     */
    private array $environment = [];

    /**
     * Executed command line.
     */
    private string $commandline;
    private array $zabbixMessageData = [];

    /**
     * Job Object.
     *
     * @param int   $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'job';
        $this->runTemplate = new RunTemplate();
        parent::__construct($identifier, $options);
        $this->setObjectName();

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->zabbixSender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
        }
    }

    /**
     * Create New Job Record in database.
     *
     * @param int    $runtemplateId Job is performed in terms of given runtemplate
     * @param array  $environment   Environment prepared for Job execution
     * @param string $scheduled     Schedule Info
     * @param string $executor      Chosen Executor class name
     *
     * @return int new job ID
     */
    public function newJob(int $runtemplateId, array $environment, string $scheduled = 'adhoc', $executor = 'Native')
    {
        $this->runTemplate->loadFromSQL($runtemplateId);
        $jobId = $this->insertToSQL([
            'runtemplate_id' => $runtemplateId,
            'company_id' => $this->runTemplate->getDataValue('company_id'),
            'app_id' => $this->runTemplate->getDataValue('app_id'),
            'env' => \serialize($environment),
            'exitcode' => -1,
            'stdout' => '',
            'stderr' => '',
            'schedule' => $scheduled,
            'executor' => $executor,
            'launched_by' => \Ease\Shared::user()->getMyKey(),
        ]);
        $environment['MULTIFLEXI_JOB_ID']['value'] = $jobId;
        $this->environment = $environment;
        $this->updateToSQL(['env' => serialize($environment), 'command' => $this->getCmdline()], ['id' => $jobId]);

        return $jobId;
    }

    /**
     * Begin the Job.
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

        if (null === $this->runTemplate) {
            $this->runTemplate = new RunTemplate();
        }

        if ($this->runTemplate->getMyKey() === 0) {
            $this->runTemplate->loadFromSQL($this->runTemplate->runTemplateID($appId, $companyId));
            $this->addStatusMessage(_('Job launched without runtemplate ID'), 'error');
        }

        $jobId = $this->getMyKey();

        // $this->addStatusMessage('JOB: ' . $jobId . ' ' . json_encode($this->environment), 'debug');
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix([
                'phase' => 'jobStart',
                'begin' => (new \DateTime())->format('Y-m-d H:i:s'),
                'interval' => $this->runTemplate->getDataValue('interv'),
                'interval_seconds' => self::codeToSeconds($this->runTemplate->getDataValue('interv')),
            ]);
        }

        $this->updateToSQL(['id' => $this->getMyKey(), 'command' => $this->executor->commandline(), 'runtemplate_id' => $this->runTemplate->getMyKey(), 'begin' => new \Envms\FluentPDO\Literal(\Ease\Shared::cfg('DB_CONNECTION') === 'sqlite' ? "date('now')" : 'NOW()')]);

        return $jobId;
    }

    /**
     * Action at Job run finish.
     *
     * @param int    $statusCode
     * @param string $stdout     Job Output
     * @param string $stderr     Job error output
     *
     * @return int
     */
    public function runEnd($statusCode, $stdout, $stderr)
    {
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany(0);
        $sqlLogger->setApplication(0);

        $resultFileField = $this->application->getDataValue('resultfile');
        $resultfile = \array_key_exists($resultFileField, $this->executor->environment) ? $this->executor->environment[$resultFileField]['value'] : '';

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->reportToZabbix([
                'phase' => 'jobDone',
                'job_id' => $this->getMyKey(),
                'company_id' => $this->runTemplate->getDataValue('company_id'),
                'company_code' => $this->company->getDataValue('code'),
                'company_name' => $this->company->getRecordName(),
                'app_id' => $this->runTemplate->getDataValue('app_id'),
                'app_name' => $this->application->getRecordName(),
                'data' => file_exists($resultfile) ? file_get_contents($resultfile) : '',
                'stdout' => $stdout,
                'stderr' => $stderr,
                'version' => $this->application->getDataValue('version'),
                'exitcode' => $statusCode,
                'end' => (new \DateTime())->format('Y-m-d H:i:s')]);
        }

        $this->setData([
            'stdout' => addslashes($stdout),
            'stderr' => addslashes($stderr),
            'command' => $this->executor->commandline(),
            'exitcode' => $statusCode,
        ]);

        $this->performActions($statusCode === 0 ? 'success' : 'fail');

        if (file_exists($resultfile)) {
            unlink($resultfile);
        }

        return $this->updateToSQL([
            'end' => new \Envms\FluentPDO\Literal(\Ease\Shared::cfg('DB_CONNECTION') === 'sqlite' ? "date('now')" : 'NOW()'),
            'stdout' => addslashes($stdout),
            'stderr' => addslashes($stderr),
            'app_version' => $this->application->getDataValue('version'),
            // 'command' => $this->commandline,
            'exitcode' => $statusCode,
        ], ['id' => $this->getMyKey()]);
    }

    /**
     * Check App Provisioning state.
     */
    public function isProvisioned(int $runtemplateId): bool
    {
        $appCompany = new RunTemplate($runtemplateId);
        $appInfo = $appCompany->getAppInfo();

        return (bool) $appInfo['prepared'];
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

    /**
     * Prepare Job for run.
     *
     * @param int    $runTemplateId ID of RunTempate to use
     * @param array  $envOverride   use to change default env [env with info]
     * @param string $scheduled     Time to launch
     * @param string $executor      Executor Class Name
     */
    public function prepareJob(int $runTemplateId, $envOverride = [], $scheduled = 'adhoc', $executor = 'Native'): string
    {
        $outline = '';
        $this->runTemplate = new RunTemplate($runTemplateId);
        $appId = $this->runTemplate->getDataValue('app_id');
        $companyId = $this->runTemplate->getDataValue('company_id');

        $this->application = $this->runTemplate->getApplication();
        LogToSQL::singleton()->setApplication($appId);

        $this->company = $this->runTemplate->getCompany();

        $this->environment = array_merge($this->getFullEnvironment(), $envOverride);
        $this->loadFromSQL($this->newJob($runTemplateId, $this->environment, $scheduled, $executor));

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
                'launched_by_id' => (int) \Ease\Shared::user()->getMyKey(),
                'launched_by' => empty(\Ease\Shared::user()->getUserLogin()) ? 'cron' :
                \Ease\Shared::user()->getUserLogin(),
            ];
        }

        $setupCommand = $this->application->getDataValue('setup');

        if ($setupCommand && $this->isProvisioned($runTemplateId) === 0) {
            $this->addStatusMessage(_('Perform initial setup'), 'warning');

            if (!empty(trim($setupCommand))) {
                $this->application->addStatusMessage(_('Setup command').': '.$setupCommand, 'debug');
                $appInfo = $this->runTemplate->getAppInfo();
                $appEnvironment = Environmentor::flatEnv($this->environment);
                $process = new \Symfony\Component\Process\Process(
                    explode(' ', $setupCommand),
                    null,
                    $appEnvironment,
                    null,
                    32767,
                );
                $result = $process->run(static function ($type, $buffer): void {
                    $logger = new Runner();

                    if ($buffer) {
                        if (\Symfony\Component\Process\Process::ERR === $type) {
                            $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                            $logger->addStatusMessage($buffer, 'error');
                        } else {
                            $logger->addStatusMessage($buffer, 'success');
                            $outline = (new \SensioLabs\AnsiConverter\AnsiToHtmlConverter())->convert($buffer);
                        }
                    }
                });

                if ($result === 0) {
                    $this->runTemplate->setProvision(1);

                    if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
                        $this->reportToZabbix(['phase' => 'setup']); // TODO: report provision done
                    }

                    $this->addStatusMessage('provision done', 'success');
                }
            }
        }

        return $outline;
    }

    /**
     * Schedule job execution.
     *
     * @return int schedule ID
     */
    public function scheduleJobRun(\DateTime $when): int
    {
        $scheduler = new Scheduler();

        return $scheduler->addJob($this, $when);
    }

    /**
     * Send Job phase Message to Zabbix.
     *
     * @param array $messageData override fields
     */
    public function reportToZabbix($messageData): bool
    {
        $packet = new ZabbixPacket();
        $hostname = \Ease\Shared::cfg('ZABBIX_HOST');
        $this->zabbixMessageData = array_merge($this->zabbixMessageData, $messageData);
        $packet->addMetric((new ZabbixMetric('job-['.$this->company->getDataValue('code').'-'.$this->application->getDataValue('code').'-'.$this->runTemplate->getMyKey().']', json_encode($this->zabbixMessageData)))->withHostname($hostname));

        try {
            $result = $this->zabbixSender->send($packet);
        } catch (\Exception $exc) {
            $result = false;
        }

        return $result;
    }

    /**
     * Perform Job.
     */
    public function performJob(): void
    {
        $this->runBegin();
        $this->executor->launchJob();
        $this->runEnd($this->executor->getExitCode(), $this->executor->getOutput(), $this->executor->getErrorOutput());
    }

    /**
     * Obtain Full Job Command Line.
     *
     * @return string command line
     */
    public function getCmdline()
    {
        return $this->application->getDataValue('executable').' '.$this->getCmdParams();
    }

    /**
     * Obtain Job Command Line Parameters.
     *
     * @return string command line parameters
     */
    public function getCmdParams()
    {
        $cmdparams = $this->application->getDataValue('cmdparams');

        if (\is_array($this->environment)) {
            foreach ($this->environment as $envKey => $envInfo) {
                $cmdparams = str_replace('{'.$envKey.'}', (string) $envInfo['value'], (string) $cmdparams);
            }
        }

        return $cmdparams;
    }

    /**
     * Obtain Job Output.
     *
     * @return string job output
     */
    public function getOutput()
    {
        return $this->getDataValue('stdout');
    }

    /**
     * Obtain Job Error Output.
     *
     * @return string job StdErr
     */
    public function getErrorOutput()
    {
        return $this->getDataValue('stderr');
    }

    public function cleanUp(): void
    {
        // TODO: Delete Uploaded files if any
    }

    /**
     * #Generate Job Launcher.
     *
     * @return string
     */
    public function launcherScript()
    {
        $launcher[] = '#!/bin/bash';
        $launcher[] = '';
        $launcher[] = '# '.\Ease\Shared::appName().' v'.\Ease\Shared::AppVersion().' job #'.$this->getMyKey().' launcher. Generated '.(new \DateTime())->format('Y-m-d H:i:s').' for company: '.$this->company->getDataValue('name');
        $launcher[] = '';
        $environment = $this->getDataValue('env') ? unserialize($this->getDataValue('env')) : [];

        foreach ($environment as $key => $envInfo) {
            $launcher[] = '';
            $launcher[] = '# Source '.$envInfo['source'];
            $launcher[] = 'export '.$key."='".$envInfo['value']."'";

            if (\array_key_exists('description', $envInfo)) {
                $launcher[] = '# '.$envInfo['description'];
            }
        }

        $launcher[] = '';
        $launcher[] = $this->application->getDataValue('executable').' '.$this->getCmdParams();

        return implode("\n", $launcher);
    }

    /**
     * Get Job Interval by Code.
     *
     * @deprecated since version 1.15.0 Use the RunTemplate::codeToInterval instead
     *
     * @param string $code
     *
     * @return string
     */
    public static function codeToInterval($code)
    {
        return \array_key_exists($code, self::$intervalCode) ? self::$intervalCode[$code] : 'n/a';
    }

    /**
     * Get Job Interval by Code.
     *
     * @deprecated since version 1.15.0 Use the RunTemplate::codeToSeconds instead
     *
     * @param string $code
     *
     * @return int Interval length in seconds
     */
    public static function codeToSeconds($code)
    {
        return \array_key_exists($code, self::$intervalSecond) ? (int) (self::$intervalSecond[$code]) : 0;
    }

    /**
     * Get Interval code by Name.
     *
     * @deprecated since version 1.15.0 Use the RunTemplate::intervalToCode instead
     *
     * @param string $interval
     *
     * @return string
     */
    public static function intervalToCode($interval)
    {
        return \array_key_exists($interval, array_flip(self::$intervalCode)) ? array_flip(self::$intervalCode)[$interval] : 'n/a';
    }

    /**
     * Current Job Environment.
     *
     * @return array
     */
    public function getEnv()
    {
        return $this->environment;
    }

    /**
     * Gives Full Environment with Full info.
     *
     * @return array Environment with metadata
     */
    public function getFullEnvironment(): array
    {
        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');
        $jobEnv = [];

        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\'.$injector;
            $jobEnv = array_merge($jobEnv, (new $injectorClass($this))->getEnvironment());
        }

        foreach (array_keys($jobEnv) as $fieldName) {
            if (\array_key_exists('value', $jobEnv[$fieldName]) && preg_match('/({[A-Z_]*})/', (string) $jobEnv[$fieldName]['value'])) {
                $jobEnv[$fieldName]['value'] = Conffield::applyMarcros($jobEnv[$fieldName]['value'], $jobEnv);
            }

            if (\array_key_exists('defval', $jobEnv[$fieldName]) && preg_match('/({[A-Z_]*})/', (string) $jobEnv[$fieldName]['defval'])) {
                $jobEnv[$fieldName]['defval'] = Conffield::applyMarcros($jobEnv[$fieldName]['defval'], $jobEnv);
            }
        }

        if (\array_key_exists('RESULT_FILE', $jobEnv)) {
            $jobEnv['RESULT_FILE']['value'] = sys_get_temp_dir().\DIRECTORY_SEPARATOR.basename($jobEnv['RESULT_FILE']['value']);
        }

        return $jobEnv;
    }

    /**
     * Generate Environment for current Job.
     *
     * @return array
     */
    public function compileEnv()
    {
        return Environmentor::flatEnv($this->getFullEnvironment());
    }

    /**
     * {@inheritDoc}
     */
    public function takeData($data): void
    {
        parent::takeData($data);

        $this->environment = empty($this->getDataValue('env')) ? [] : unserialize($this->getDataValue('env'));

        if ((null === $this->getDataValue('company_id')) === false) {
            $this->company = new Company((int) $this->getDataValue('company_id'));
        }

        if ((null === $this->getDataValue('app_id')) === false) {
            $this->application = new Application((int) $this->getDataValue('app_id'));
        }

        if (\array_key_exists('runtemplate_id', $data) && !empty($data['runtemplate_id'])) {
            $this->runTemplate->loadFromSQL($data['runtemplate_id']);
        } else {
            if ($this->application->getMyKey() && $this->company->getMyKey()) {
                $this->runTemplate->loadFromSQL($this->runTemplate->runTemplateID($this->application->getMyKey(), $this->company->getMyKey()));
                $this->addStatusMessage(_('No runtemplate ID proveided'), 'warning');
            }
        }

        if ($this->getDataValue('executor')) {
            $executorClass = '\\MultiFlexi\\Executor\\'.$this->getDataValue('executor');

            if (class_exists($executorClass)) {
                $this->executor = new $executorClass($this);
            } else {
                $this->addStatusMessage(sprintf(_('Requested Executor %s not availble'), $executorClass), 'warning');
                $this->executor = new \MultiFlexi\Executor\Native($this);
            }
        }
    }

    /**
     * export .env file content.
     *
     * @return string
     */
    public function envFile()
    {
        $launcher[] = '# '.\Ease\Shared::appName().' v'.\Ease\Shared::AppVersion().' job #'.$this->getMyKey().' environment. Generated '.(new \DateTime())->format('Y-m-d H:i:s').' for company: '.$this->company->getDataValue('name');
        $launcher[] = '';
        $environment = $this->getDataValue('env') ? unserialize($this->getDataValue('env')) : [];

        foreach ($environment as $key => $envInfo) {
            $launcher[] = $key."='".$envInfo['value']."'";
        }

        return implode("\n", $launcher);
    }

    /**
     * Perform Actions For any mode.
     *
     * @param mixed $mode
     * @param string success | fail
     */
    public function performActions($mode): void
    {
        $actions = $this->runTemplate->getDataValue($mode) ? unserialize($this->runTemplate->getDataValue($mode)) : [];
        $modConf = new ModConfig();
        $actConf = new \MultiFlexi\ActionConfig();
        $modConfigs = $actConf->getRuntemplateConfig($this->runTemplate->getMyKey())->where('mode', $mode)->fetchAll();

        foreach ($actions as $action => $enabled) {
            $actionClass = '\\MultiFlexi\\Action\\'.$action;

            if ($enabled && class_exists($actionClass)) {
                $actionHandler = new $actionClass($this->runTemplate);
                $actionHandler->setData($modConf->getModuleConf($action));

                foreach ($modConfigs as $modConfig) {
                    if ($action === $modConfig['module']) {
                        $actionHandler->setDataValue($modConfig['keyname'], $modConfig['value']);
                    }
                }

                $actionHandler->perform($this);
            }
        }
    }

    public function getNextJobId(bool $keepApp = true, bool $keepRuntemplate = true, bool $keepCompnay = true): int
    {
        $condition = [];

        if ($keepApp) {
            $condition['app_id'] = $this->getDataValue('app_id');
        }

        if ($keepRuntemplate) {
            $condition['runtemplate_id'] = $this->getDataValue('runtemplate_id');
        }

        if ($keepCompnay) {
            $condition['company_id'] = $this->getDataValue('company_id');
        }

        $nextJobId = $this->listingQuery()->select('id', true)->where($condition)->where('id > '.$this->getMyKey())->orderBy('id')->limit(1)->fetchColumn();

        return $nextJobId ? $nextJobId : 0;
    }

    public function getPreviousJobId(bool $keepApp = true, bool $keepRuntemplate = true, bool $keepCompnay = true): int
    {
        $condition = [];

        if ($keepApp) {
            $condition['app_id'] = $this->getDataValue('app_id');
        }

        if ($keepRuntemplate) {
            $condition['runtemplate_id'] = $this->getDataValue('runtemplate_id');
        }

        if ($keepCompnay) {
            $condition['company_id'] = $this->getDataValue('company_id');
        }

        $prevJobId = $this->listingQuery()->select('id', true)->where($condition)->where('id < '.$this->getMyKey())->orderBy('id DESC')->limit(1)->fetchColumn();

        return $prevJobId ? $prevJobId : 0;
    }

    public function getEnvironment(): array
    {
        return $this->environment;
    }
}
