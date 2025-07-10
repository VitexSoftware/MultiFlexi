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
    private ConfigFields $environment;

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
        $this->nameColumn = '';
        $this->runTemplate = new RunTemplate();
        $this->environment = new ConfigFields(_('Job Env'));

        parent::__construct($identifier, $options);
        $this->setObjectName();

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->zabbixSender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
            $this->setZabbixValue('phase', 'loaded');
            $this->setZabbixValue('job_id', $this->getMyKey());
            $this->setZabbixValue('app_id', null);
            $this->setZabbixValue('app_name', null);
            $this->setZabbixValue('begin', null);
            $this->setZabbixValue('end', null);
            $this->setZabbixValue('scheduled', null);
            $this->setZabbixValue('schedule_type', null);
            $this->setZabbixValue('company_id', null);
            $this->setZabbixValue('company_name', null);
            $this->setZabbixValue('company_code', null);
            $this->setZabbixValue('runtemplate_id', null);
            $this->setZabbixValue('exitcode', null);
            $this->setZabbixValue('stdout', null);
            $this->setZabbixValue('stderr', null);
            $this->setZabbixValue('executor', null);
            $this->setZabbixValue('launched_by_id', null);
            $this->setZabbixValue('launched_by', null);
            $this->setZabbixValue('data', null);
            $this->setZabbixValue('pid', null);
            $this->setZabbixValue('interval_seconds', null);
        }
    }

    /**
     * Create New Job Record in database.
     *
     * @param int          $runtemplateId Job is performed in terms of given RunTemplate
     * @param ConfigFields $environment   Environment prepared for Job execution
     * @param \DateTime    $scheduled     Schedule Timestamp
     * @param string       $executor      Chosen Executor class name
     * @param string       $scheduleType  Schedule type Info
     *
     * @return int new job ID
     */
    public function newJob(int $runtemplateId, ConfigFields $environment, \DateTime $scheduled, $executor = 'Native', $scheduleType = 'adhoc')
    {
        $this->runTemplate->loadFromSQL($runtemplateId);
        $jobId = $this->insertToSQL([
            'runtemplate_id' => $runtemplateId,
            'company_id' => $this->runTemplate->getDataValue('company_id'),
            'app_id' => $this->runTemplate->getDataValue('app_id'),
            'env' => \serialize($environment),
            'exitcode' => null,
            'stdout' => '',
            'stderr' => '',
            'schedule' => $scheduled->format('Y-m-d H:i:s'),
            'schedule_type' => $scheduleType,
            'executor' => $executor,
            'launched_by' => \Ease\Shared::user()->getMyKey(),
        ]);

        $environment->addField((new ConfigField('MULTIFLEXI_JOB_ID', 'integer', _('Job ID'), _('Number of job'), '', (string) $jobId))->setSource(self::class));

        $this->environment = $environment;
        $this->updateToSQL(['env' => serialize($environment), 'command' => $this->getCmdline()], ['id' => $jobId]);

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->setZabbixValue('phase', 'created');
            $this->setZabbixValue('job_id', $jobId);
            $this->setZabbixValue('app_id', $this->runTemplate->getDataValue('app_id'));
            $this->setZabbixValue('app_name', $this->runTemplate->getApplication()->getDataValue('name'));
            $this->setZabbixValue('company_id', $this->runTemplate->getDataValue('company_id'));
            $this->setZabbixValue('company_name', $this->runTemplate->getCompany()->getDataValue('name'));
            $this->setZabbixValue('company_code', $this->runTemplate->getCompany()->getDataValue('code'));
            $this->setZabbixValue('runtemplate_id', $runtemplateId);
            $this->setZabbixValue('executor', $executor);

            $this->reportToZabbix($this->zabbixMessageData);
        }

        return $jobId;
    }

    /**
     * Begin the Job.
     *
     * @return int Job ID
     */
    public function runBegin()
    {
        $appId = $this->getRunTemplate()->getApplication()->getMyKey();
        $companyId = $this->company->getMyKey();
        $this->setObjectName();
        $sqlLogger = LogToSQL::singleton();
        $sqlLogger->setCompany($companyId);
        $sqlLogger->setApplication($appId);

        if (null === $this->runTemplate) {
            throw new \Ease\Exception(_('No RunTemplate prepared'));
        }

        if ($this->runTemplate->getMyKey() === 0) {
            throw new \Ease\Exception(_('No RunTemplate prepared'));
        }

        $this->environment = $this->getJobEnvironment();

        if (isset($this->executor) === false) {
            $executorClass = '\\MultiFlexi\\Executor\\'.$this->getDataValue('executor');
            $this->executor = new $executorClass($this);
        } else {
            $this->executor->setJob($this);
        }

        $jobId = $this->getMyKey();

        // $this->addStatusMessage('JOB: ' . $jobId . ' ' . json_encode($this->environment), 'debug');
        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->setZabbixValue('phase', 'jobStart');
            $this->setZabbixValue('executor', $this->getDataValue('executor'));
            $this->setZabbixValue('begin', (new \DateTime())->format('Y-m-d H:i:s'));
            $this->setZabbixValue('interval', $this->runTemplate->getDataValue('interv'));
            $this->setZabbixValue('interval_seconds', self::codeToSeconds($this->runTemplate->getDataValue('interv')));
            $this->setZabbixValue('app_name', $this->runTemplate->getApplication()->getRecordName());
            $this->setZabbixValue('app_id', $this->runTemplate->getDataValue('app_id'));
            $this->setZabbixValue('runtemplate_id', $this->runTemplate->getMyKey());
            $this->setZabbixValue('runtemplate_name', $this->runTemplate->getRecordName());
            $this->setZabbixValue('launched_by_id', (int) \Ease\Shared::user()->getMyKey());
            $this->setZabbixValue('launched_by', empty(\Ease\Shared::user()->getUserLogin()) ? 'cron' : \Ease\Shared::user()->getUserLogin());

            $this->setZabbixValue('scheduled', $this->getDataValue('schedule'));
            // $this->zabbixMessageData['schedule_type'] => $scheduleType,
            $this->setZabbixValue('company_id', $this->company->getMyKey());
            $this->setZabbixValue('company_name', $this->company->getDataValue('name'));
            $this->setZabbixValue('company_code', $this->company->getDataValue('code'));
            $this->reportToZabbix($this->zabbixMessageData);
        }

        $this->updateToSQL(['id' => $this->getMyKey(), 'env' => serialize($this->environment), 'command' => $this->executor->commandline(), 'runtemplate_id' => $this->runTemplate->getMyKey(), 'begin' => new \Envms\FluentPDO\Literal(\Ease\Shared::cfg('DB_CONNECTION') === 'sqlite' ? "date('now')" : 'NOW()')]);

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

        if ($this->environment->getFieldByCode($resultFileField)) {
            $resultfile = $this->environment->getFieldByCode($resultFileField)->getValue();
        } else {
            $resultfile = '';
        }

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->setZabbixValue('phase', 'jobDone');
            $this->setZabbixValue('data', file_exists($resultfile) ? file_get_contents($resultfile) : '');
            $this->setZabbixValue('stdout', $stdout);
            $this->setZabbixValue('stderr', $stderr);
            $this->setZabbixValue('version', $this->application->getDataValue('version'));
            $this->setZabbixValue('exitcode', $statusCode);
            $this->setZabbixValue('scheduled', $this->getDataValue('schedule'));
            $this->setZabbixValue('end', (new \DateTime())->format('Y-m-d H:i:s'));
            $this->setZabbixValue('runtemplate_id', $this->runTemplate->getMyKey());
            $this->setZabbixValue('pid', $this->executor->getPid());
            $this->reportToZabbix($this->zabbixMessageData);
        }

        $this->setData([
            'pid' => $this->executor->getPid(),
            'stdout' => addslashes($stdout),
            'stderr' => addslashes($stderr),
            'command' => $this->executor->commandline(),
            'exitcode' => $statusCode,
        ]);

        $this->performActions($statusCode === 0 ? 'success' : 'fail');

        // TODO
        //        if (file_exists($resultfile)) {
        //            unlink($resultfile);
        //        }

        return $this->updateToSQL([
            'pid' => $this->executor->getPid(),
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
     * @param int          $runTemplateId ID of RunTempate to use
     * @param ConfigFields $envOverride   use to change default env [env with info]
     * @param \DateTime    $scheduled     Time to launch
     * @param string       $executor      Executor Class Name
     */
    public function prepareJob(int $runTemplateId, ConfigFields $envOverride, \DateTime $scheduled, string $executor = 'Native', string $scheduleType = 'adhoc'): string
    {
        $outline = '';
        $this->runTemplate = new RunTemplate($runTemplateId);
        $appId = $this->runTemplate->getDataValue('app_id');
        $companyId = $this->runTemplate->getDataValue('company_id');

        $this->application = $this->runTemplate->getApplication();
        LogToSQL::singleton()->setApplication($appId);

        $this->company = $this->runTemplate->getCompany();
        $this->setDataValue('executor', $executor);
        $this->environment->addFields($this->getFullEnvironment());
        $this->environment->addFields($envOverride);

        $this->loadFromSQL($this->newJob($runTemplateId, $this->environment, $scheduled, $executor, $scheduleType));

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->setZabbixValue('phase', 'prepared');
            $this->setZabbixValue('job_id', $this->getMyKey());
            $this->setZabbixValue('app_id', $appId);
            $this->setZabbixValue('app_name', $this->application->getDataValue('name'));
            $this->setZabbixValue('begin', null);
            $this->setZabbixValue('end', null);
            $this->setZabbixValue('scheduled', $scheduled->format('Y-m-d H:i:s'));
            $this->setZabbixValue('schedule_type', $scheduleType);
            $this->setZabbixValue('company_id', $companyId);
            $this->setZabbixValue('company_name', $this->company->getDataValue('name'));
            $this->setZabbixValue('company_code', $this->company->getDataValue('code'));
            $this->setZabbixValue('runtemplate_id', $runTemplateId);
            $this->setZabbixValue('exitcode', null);
            $this->setZabbixValue('stdout', null);
            $this->setZabbixValue('stderr', null);
            $this->setZabbixValue('executor', $executor);
            $this->setZabbixValue('launched_by_id', (int) \Ease\Shared::user()->getMyKey());
            $this->setZabbixValue('launched_by', empty(\Ease\Shared::user()->getUserLogin()) ? 'cron' : \Ease\Shared::user()->getUserLogin());
            $this->reportToZabbix($this->zabbixMessageData);
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
        $this->addStatusMessage(_('Scheduling job').': '.$when->format('Y-m-d H:i:s'));
        $scheduler = new Scheduler();

        return $scheduler->addJob($this, $when);
    }

    /**
     * Send Job phase Message to Zabbix.
     *
     * @param array<string, mixed> $messageData override fields
     */
    public function reportToZabbix(array $messageData): bool
    {
        $packet = new ZabbixPacket();

        $overrideHost = $this->getRunTemplate()->getCompany()->getDataValue('zabbix_host');

        $hostname = empty($overrideHost) ? \Ease\Shared::cfg('ZABBIX_HOST', gethostname()) : $overrideHost;
        $itemKey = 'job-['.$this->company->getDataValue('slug').'-'.$this->application->getDataValue('code').'-'.$this->runTemplate->getMyKey().']';

        $zabbixMetric = json_encode($this->zabbixMessageData);

        if ($zabbixMetric) {
            $packet->addMetric((new ZabbixMetric($itemKey, $zabbixMetric))->withHostname($hostname));

            // file_put_contents('/tmp/zabbix-' . $this->zabbixMessageData['phase'] .'-'. $this->getMyKey().'-'. time().'.json' , json_encode($this->zabbixMessageData));

            try {
                $result = $this->zabbixSender->send($packet);

                if ($this->debug) {
                    $this->addStatusMessage('Data Sent To Zabbix: '.$itemKey.' '.json_encode($messageData), 'debug');
                }
            } catch (\Exception $exc) {
                $result = false;
            }
        } else {
            $this->addStatusMessage('Problem Jsonizing of '.serialize($this->zabbixMessageData), 'debug');
        }

        return (bool) $result;
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

        foreach ($this->environment as $envKey => $field) {
            $cmdparams = str_replace('{'.$envKey.'}', (string) $field->getValue(), (string) $cmdparams);
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
        asort($environment);

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
    public function getFullEnvironment(): ConfigFields
    {
        $jobEnvironment = new ConfigFields(sprintf(_('Job #%d'), $this->getMyKey()));

        \Ease\Functions::loadClassesInNamespace('MultiFlexi\\Env');
        $injectors = \Ease\Functions::classesInNamespace('MultiFlexi\\Env');

        foreach ($injectors as $injector) {
            $injectorClass = '\\MultiFlexi\\Env\\'.$injector;
            $jobEnvironment->addFields((new $injectorClass($this))->getEnvironment());
        }

        foreach ($jobEnvironment->getFields() as $fieldName => $field) {
            $fieldValue = $field->getValue();

            if (null === $fieldValue) {
                $fieldValue = $field->getDefaultValue();
            }

            if ($fieldValue) {
                if ($fieldValue && preg_match('/({[A-Z_]*})/', $fieldValue)) {
                    $field->setValue(self::applyMarcros($fieldValue, $jobEnvironment));
                }
            }
        }

        $resultFileField = $jobEnvironment->getFieldByCode('RESULT_FILE');

        if ($resultFileField) {
            $resultFile = $resultFileField->getValue();

            if ($resultFile === sys_get_temp_dir()) {
                $resultFileField->setValue($resultFile.\DIRECTORY_SEPARATOR.\Ease\Functions::randomString());
            } else {
                $resultFileField->setValue(sys_get_temp_dir().\DIRECTORY_SEPARATOR.basename($resultFile));
            }
        }

        return $jobEnvironment;
    }

    /**
     * Populate template by values from environment.
     */
    public static function applyMarcros(string $template, ConfigFields $fields): string
    {
        $hydrated = $template;

        foreach ($fields->getFields() as $envKey => $envField) {
            $value = method_exists($envField, 'getValue') ? $envField->getValue() : '';
            $hydrated = str_replace('{'.$envKey.'}', (string) $value, $hydrated);
        }

        return $hydrated;
    }

    /**
     * Generate Environment for current Job.
     */
    public function compileEnv(): array
    {
        return Environmentor::flatEnv($this->getFullEnvironment());
    }

    /**
     * {@inheritDoc}
     */
    #[\Override]
    public function takeData(array $data): int
    {
        parent::takeData($data);

        if ($this->getDataValue('env')) {
            if (\Ease\Functions::isSerialized($this->getDataValue('env'))) {
                $envUnserialized = unserialize($this->getDataValue('env'));

                if (\is_array($envUnserialized)) { // Old Serialization method fallback
                    foreach ($envUnserialized as $key => $envInfo) {
                        $field = new ConfigField($key, 'string', $key, '', '', (string) $envInfo['value']);
                        $field->setSource($envInfo['source']);
                        $this->environment->addField($field);
                    }
                } else {
                    $this->environment->addFields($envUnserialized);
                }
            }
        }

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

        return \count($data);
    }

    /**
     * export .env file content.
     */
    public function envFile(): string
    {
        $creds = $this->runTemplate->credentialsEnvironment();

        $launcher[] = '# '.\Ease\Shared::appName().' v'.\Ease\Shared::AppVersion().' Job 🏁 #'.$this->getMyKey().' environment. Generated '.(new \DateTime())->format('Y-m-d H:i:s').' for company: '.$this->company->getDataValue('name');
        $launcher[] = '';

        if ($this->getDataValue('env')) {
            foreach (unserialize($this->getDataValue('env')) as $configFieldName => $configFieldInfo) {
                $field = $creds->getFieldByCode($configFieldName);

                if (null === $field) {
                    $creds->addField(new ConfigField($configFieldName, 'string', $configFieldName, '', '', \is_array($configFieldInfo) ? (string) $configFieldInfo['value'] : $configFieldInfo->getValue()));
                } else {
                    if (\is_object($configFieldInfo)) {
                        $creds->addField($configFieldInfo);
                    } else {
                        $field->setValue($configFieldInfo['value']);
                        $field->setSource($configFieldInfo['source']);
                    }
                }
            }
        }

        foreach ($creds->getEnvArray() as $key => $value) {
            $launcher[$key] = $key."='".$value."'";
        }

        ksort($launcher);

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

    public function getEnvironment(): ConfigFields
    {
        return $this->environment;
    }

    public function setZabbixValue(string $field, $value): self
    {
        $this->zabbixMessageData[$field] = $value;

        return $this;
    }

    public function setPid(int $pid): void
    {
        $this->setDataValue('pid', $pid);
        $this->setZabbixValue('pid', $pid);
    }

    public function setEnvironment(ConfigFields $environment): self
    {
        $this->environment = $environment;

        return $this;
    }

    public function todaysCond(string $column = 'begin'): string
    {
        $databaseType = $this->getPdo()->getAttribute(\PDO::ATTR_DRIVER_NAME);

        switch ($databaseType) {
            case 'mysql':
                $cond = ('DATE('.$column.') = CURDATE()');

                break;
            case 'sqlite':
                $cond = ('DATE('.$column.') = DATE(\'now\')');

                break;
            case 'pgsql':
                $cond = ('DATE('.$column.') = CURRENT_DATE');

                break;
            case 'sqlsrv':
                $cond = ('CAST('.$column.' AS DATE) = CAST(GETDATE() AS DATE)');

                break;

            default:
                throw new \Exception('Unsupported database type '.$databaseType);
        }

        return $cond;
    }

    public function getJobEnvironment(): ConfigFields
    {
        // Assembly Enviromnent from
        // 0 Current - default
        // 1 Company
        // 2 Runtemplate

        $jobEnvironment = new ConfigFields(sprintf(_('Job #%d'), $this->getMyKey()));
        $jobEnvironment->addFields($this->company->getEnvironment());
        $jobEnvironment->addFields($this->runTemplate->getEnvironment());

        return $jobEnvironment;
    }

    public function getRunTemplate(): ?RunTemplate
    {
        return $this->runTemplate;
    }

    public function setRunTemplate(RunTemplate $runtemplate): self
    {
        $this->runTemplate = $runtemplate;

        return $this;
    }
}
