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
 * @author vitex
 */
class RunTemplate extends \MultiFlexi\DBEngine
{
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
    public Application $application;

    /**
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->nameColumn = 'name';
        $this->myTable = 'runtemplate';
        parent::__construct($identifier, $options);
    }

    /**
     * Get Job Interval by Code.
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
     */
    public static function intervalToCode(string $interval): string
    {
        return \array_key_exists($interval, array_flip(self::$intervalCode)) ? array_flip(self::$intervalCode)[$interval] : 'n/a';
    }

    /**
     * Get id by App & Company.
     *
     * SELECT runtemplate.id, runtemplate.interv, runtemplate.prepared, apps.name AS app, company.name AS company   FROM runtemplate LEFT JOIN apps ON runtemplate.app_id=apps.id LEFT JOIN company ON runtemplate.company_id=company.id;
     *
     * @deprecated since version 1.0
     *
     * @return int
     */
    public function runTemplateID(int $appId, int $companyId)
    {
        $runTemplateId = (int) $this->listingQuery()->where('company_id='.$companyId.' AND app_id='.$appId)->select('id', true)->fetchColumn();

        return $runTemplateId ? $runTemplateId : $this->dbsync(['app_id' => $appId, 'company_id' => $companyId, 'interv' => 'n']);
    }

    /**
     * Set APP State.
     */
    public function setState(bool $state): bool
    {
        if ($state === false) {
            $this->setDataValue('interv', 'n');
        }

        $changed = $this->dbsync();

        if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            $this->notifyZabbix($this->getData());
        }

        return $changed;
    }

    public function performInit(): void
    {
        $app = new Application((int) $this->getDataValue('app_id'));

        //        $this->setEnvironment();
        if (empty($app->getDataValue('setup')) === false) {
            $this->setDataValue('prepared', 0);
            $this->dbsync();
        }
        //        $app->runInit();
    }

    /**
     * Delete record ignoring interval.
     *
     * @param array $data
     *
     * @return int
     */
    public function deleteFromSQL($data = null)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $arnold = new \MultiFlexi\ActionConfig();
        $arnold->deleteFromSQL(['runtemplate_id' => $this->getMyKey()]);

        $configurator = new \MultiFlexi\Configuration();
        $configurator->deleteFromSQL(['runtemplate_id' => $this->getMyKey()]);

        $rtpl = new \MultiFlexi\RunTplCreds();
        $rtpl->deleteFromSQL(['runtemplate_id' => $this->getMyKey()]);
        $rtpl->setmyTable('runtemplate_topics');
        $rtpl->deleteFromSQL(['runtemplate_id' => $this->getMyKey()]);

        return parent::deleteFromSQL($data);
    }

    public function getCompanyEnvironment()
    {
        $connectionData = $this->getAppInfo();

        if ($connectionData['type']) {
            $platformHelperClass = '\\MultiFlexi\\'.$connectionData['type'].'\\Company';
            $platformHelper = new $platformHelperClass($connectionData['company_id'], $connectionData);

            return $platformHelper->getEnvironment();
        }

        return [];
    }

    /**
     * @param int $companyId
     *
     * @return \Envms\FluentPDO\Query
     */
    public function getCompanyTemplates($companyId)
    {
        return $this->listingQuery()->select(['apps.name AS app_name', 'apps.description', 'apps.homepage', 'apps.uuid'])->leftJoin('apps ON apps.id = runtemplate.app_id')->where('company_id', $companyId);
    }

    /**
     * Get apps for given company sorted by.
     *
     * @return array<array>
     */
    public function getCompanyRunTemplatesByInterval(int $companyId)
    {
        $runtemplates = [
            'i' => [],
            'h' => [],
            'd' => [],
            'w' => [],
            'm' => [],
            'y' => [],
        ];

        foreach ($this->getCompanyTemplates($companyId)->fetchAll() as $template) {
            $runtemplates[$template['interv']][$template['id']] = $template;
        }

        return $runtemplates;
    }

    public static function getIntervalEmoji(string $interval): string
    {
        $emojis = [
            'n' => '🔴',
            'i' => '⏳',
            'h' => '🕰️',
            'd' => '☀️',
            'w' => '📅',
            'm' => '🌛',
            'y' => '🎆',
            '' => '',
        ];

        return \array_key_exists($interval, $emojis) ? $emojis[$interval] : '';
    }

    /**
     * @return array
     */
    public function getAppEnvironment()
    {
        $appInfo = $this->getAppInfo();
        $jobber = new Job();
        $jobber->company = new Company((int) $appInfo['company_id']);
        $jobber->application = new Application((int) $appInfo['app_id']);
        $jobber->runTemplate = $this;

        return $jobber->getFullEnvironment();
    }

    public function loadEnvironment(): void
    {
        $this->setEnvironment($this->getRuntemplateEnvironment());
    }

    /**
     * @return array
     */
    public function getAppInfo()
    {
        return $this->listingQuery()
            ->select('apps.*')
            ->select('apps.id as apps_id')
            ->select('apps.name as app_name')
            ->select('runtemplate.name as runtemplate_name')
            ->select('company.*')
            ->select('servers.*')
            ->where([$this->getMyTable().'.'.$this->getKeyColumn() => $this->getMyKey()])
            ->leftJoin('apps ON apps.id = runtemplate.app_id')
            ->leftJoin('company ON company.id = runtemplate.company_id')
            ->leftJoin('servers ON servers.id = company.server')
            ->fetch();
    }

    /**
     * All RunTemplates for GivenCompany.
     *
     * @param int $companyID
     *
     * @return array
     */
    public function getRunTemplatesForCompany($companyID)
    {
        return $this->listingQuery()->select(['app_id', 'interv', 'id'])->where(['company_id' => $companyID]);
    }

    /**
     * All RunTemplates for GivenCompany.
     *
     * @param int $companyID
     *
     * @return array
     */
    public function getActiveRunTemplatesForCompany($companyID)
    {
        return $this->getRunTemplatesForCompany($companyID)->where('runtemplate.active', true);
    }

    /**
     * Set Provision state.
     *
     * @param null|int $status 0: Unprovisioned, 1: provisioned,
     *
     * @return bool save status
     */
    public function setProvision($status)
    {
        return $this->dbsync(['prepared' => $status]);
    }

    public function setPeriods(int $companyId, array $runtemplateIds, string $interval): void
    {
        foreach ($runtemplateIds as $runtemplateId) {
            $this->updateToSQL(['interv' => $interval], ['id' => $runtemplateId]);
            //                if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
            //                    $this->notifyZabbix(['id' => $appInserted, 'app_id' => $appId, 'company_id' => $companyId, 'interv' => $interval]);
            //                }
            //                $this->addStatusMessage(sprintf(_('Application %s in company %s assigned to interval %s'), $appId, $companyId, $interval));
        }
    }

    public function notifyZabbix(array $jobInfo)
    {
        $zabbixSender = new ZabbixSender(\Ease\Shared::cfg('ZABBIX_SERVER'));
        $hostname = \Ease\Shared::cfg('ZABBIX_HOST');
        $company = new Company($jobInfo['company_id']);
        $application = new Application($jobInfo['app_id']);

        $packet = new ZabbixPacket();
        $packet->addMetric((new ZabbixMetric('job-['.$company->getDataValue('code').'-'.$application->getDataValue('code').'-'.$jobInfo['id'].'-interval]', $jobInfo['interv']))->withHostname($hostname));

        try {
            $zabbixSender->send($packet);
        } catch (\Exception $ex) {
        }

        $packet = new ZabbixPacket();
        $packet->addMetric((new ZabbixMetric('job-['.$company->getDataValue('code').'-'.$application->getDataValue('code').'-'.$jobInfo['id'].'-interval_seconds]', (string) Job::codeToSeconds($jobInfo['interv'])))->withHostname($hostname));

        try {
            $result = $zabbixSender->send($packet);
        } catch (\Exception $ex) {
        }

        return $result;
    }

    /**
     * Return only key=>value pairs.
     *
     * @return array
     */
    public static function stripToValues(array $envData)
    {
        $env = [];

        foreach ($envData as $key => $data) {
            $env[$key] = $data['value'];
        }

        return $env;
    }

    /**
     * Actions available with flag when performed in case of success of failure.
     *
     * @return array<array>
     */
    public function getPostActions()
    {
        $actions = [];
        $s = $this->getDataValue('success') ? unserialize($this->getDataValue('success')) : [];
        $f = $this->getDataValue('fail') ? unserialize($this->getDataValue('fail')) : [];

        foreach ($s as $action => $enabled) {
            $actions[$action]['success'] = $enabled;
        }

        foreach ($f as $action => $enabled) {
            $actions[$action]['fail'] = $enabled;
        }

        return $actions;
    }

    public function getApplication(): Application
    {
        $appId = $this->getDataValue('app_id');

        if (isset($this->application) === false) {
            $this->application = new Application($appId);
        }

        if ($this->application->getMyKey() !== $appId) {
            $this->application->loadFromSQL($appId);
        }

        return $this->application;
    }

    /**
     * @return \MultiFlexi\Company
     */
    public function getCompany()
    {
        return new Company($this->getDataValue('company_id'));
    }

    public function getRuntemplateEnvironment(): ConfigFields
    {
        $runtemplateEnv = new ConfigFields(sprintf(_('RunTemplate %s'), $this->getRecordName()));
        $configurator = new Configuration();
        $cfg = $configurator->listingQuery()->select(['name', 'value', 'type'], true)->where(['runtemplate_id' => $this->getMyKey()])->fetchAll('name');

        foreach ($cfg as $conf) {
            $field = new ConfigField($conf['name'], \MultiFlexi\Conffield::fixType($conf['type']), $conf['name']);
            $field->setValue($conf['value']);
            $runtemplateEnv->addField($field);
        }

        return $runtemplateEnv;
    }

    public function setEnvironment(array $properties): bool
    {
        $companies = new Company($this->getDataValue('company_id'));
        $app = new Application($this->getDataValue('app_id'));

        $app->setDataValue('company_id', $companies->getMyKey());
        $app->setDataValue('app_id', $app->getMyKey());
        $app->setDataValue('app_name', $app->getRecordName());

        $configurator = new \MultiFlexi\Configuration([
            'runtemplate_id' => $this->getMyKey(),
            'app_id' => $app->getMyKey(),
            'company_id' => $companies->getMyKey(),
        ], ['autoload' => false]);

        if ($app->checkRequiredFields($properties, true) && $configurator->takeData($properties) && null !== $configurator->saveToSQL()) {
            $configurator->addStatusMessage(_('Config fields Saved').' '.implode(',', array_keys($properties)), 'success');
            $result = true;
        } else {
            $configurator->addStatusMessage(_('Error saving Config fields'), 'error');
            $result = false;
        }

        return $result;
    }

    public static function actionIcons(?array $actions, array $properties = []): \Ease\Html\SpanTag
    {
        $icons = new \Ease\Html\SpanTag(null, $properties);

        if (\is_array($actions)) {
            foreach ($actions as $class => $status) {
                if ($status === true) {
                    $actionClass = '\\MultiFlexi\\Action\\'.$class;
                    $icons->addItem(new \Ease\Html\ImgTag($actionClass::logo(), $actionClass::name(), ['title' => $actionClass::name()."\n".$actionClass::description(), 'style' => 'height: 15px;']));
                }
            }
        }

        return $icons;
    }

    /**
     * @deprecated since version 1.27
     */
    public function legacyCredentialsEnvironment(): ConfigFields
    {
        $credentialsEnv = new ConfigFields(_('RunTemplate Legacy Credentials'));

        $credentials = [];
        $rtplCrds = new RunTplCreds();
        $kredenc = new Credential();

        foreach ($rtplCrds->getCredentialsForRuntemplate($this->getMyKey())->select(['name', 'formType', 'credentials_id'])->leftJoin('credentials ON credentials.id = runtplcreds.credentials_id')->where('credential_type_id', 0) as $crds) {
            $kredenc->dataReset();
            $kredenc->loadFromSQL($crds['credentials_id']);
            $creds = $kredenc->getData();
            unset($creds['id'], $creds['name'], $creds['company_id'], $creds['formType']);
            $this->addStatusMessage('Legacy Credentials: '.$crds['formType'].' "'.$crds['name'].'" '.implode(',', array_keys($creds)), 'debug');

            foreach ($creds as $key => $value) {
                $credentials[$key]['credential_type'] = $crds['formType'];
                $credentials[$key]['credential_id'] = $crds['id'];
                $field = new ConfigField($key, 'string', $crds['name'], '');
                $field->setSource((string) $crds['formType'])->setValue((string) $value);
                $credentialsEnv->addField($field);
            }
        }

        return $credentialsEnv;
    }

    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID')],
            ['name' => 'active', 'type' => 'boolean', 'label' => _('Active')],
            ['name' => 'interv', 'type' => 'text', 'label' => _('Interval')],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            //                    ['name' => 'resolved', 'type' => 'datetime', 'label' => _('Resolved')],
            ['name' => 'app_id', 'type' => 'selectize', 'label' => _('Application'),
                'listingPage' => 'apps.php',
                'detailPage' => 'app.php',
                //                'idColumn' => 'apps.id',
                //                'valueColumn' => 'apps.name',
                'engine' => '\MultiFlexi\Application',
                'filterby' => 'name',
            ],
            ['name' => 'company_id', 'type' => 'selectize', 'label' => _('Company'),
                'listingPage' => 'companies.php',
                'detailPage' => 'company.php',
                //                'idColumn' => 'company',
                //                'valueColumn' => 'company.name',
                'engine' => '\MultiFlexi\Company',
                'filterby' => 'name',
            ],
            ['name' => 'delay', 'type' => 'text', 'label' => _('Delay')],
            ['name' => 'executor', 'type' => 'text', 'label' => _('Executor')],
            //            ['name' => 'created', 'type' => 'datetime', 'label' => _('Created')],
        ]);
    }

    /**
     * export .env file content.
     */
    public function envFile(): string
    {
        $launcher[] = '# runtemplate #'.$this->getMyKey().' environment '.$this->getRecordName();
        $launcher[] = '# '.\Ease\Shared::appName().' v'.\Ease\Shared::AppVersion().' Generated '.(new \DateTime())->format('Y-m-d H:i:s').' for company: '.$this->getCompany()->getDataValue('name');
        $launcher[] = '';

        foreach ($this->getEnvironment()->getEnvArray() as $key => $value) {
            $launcher[] = $key."='".$value."'";
        }

        return implode("\n", $launcher);
    }

    public function completeDataRow(array $dataRowRaw): array
    {
        $dataRowRaw['interv'] = '<span title="'._(self::codeToInterval($dataRowRaw['interv'])).'">'.self::getIntervalEmoji($dataRowRaw['interv']).' '._(self::codeToInterval($dataRowRaw['interv'])).'</span>';
        $dataRowRaw['active'] = (string) $dataRowRaw['active'] ? '&nbsp;<a href="schedule.php?id='.$dataRowRaw['id'].'&when=now&executor=Native" title="'._('Launch now').'"><span style="color: green; font-weight: xx-large;">▶</span></a> ' : '<span style="color: lightgray; font-weight: xx-large;" title="'._('Disabled').'">🚧</span>';
        $dataRowRaw['name'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '<strong>'.$dataRowRaw['name'].'</strong>');
        $dataRowRaw['id'] = (string) new \Ease\Html\ATag('runtemplate.php?id='.$dataRowRaw['id'], '⚗️ #'.$dataRowRaw['id']);

        $dataRowRaw['executor'] = (string) new Ui\ExecutorImage($dataRowRaw['executor'], ['style' => 'height: 40px;']);
        $dataRowRaw['app_id'] = (string) new Ui\AppLinkButton(new Application((int) $dataRowRaw['app_id']), ['style' => 'height: 40px;']);
        $dataRowRaw['company_id'] = (string) new Ui\CompanyLinkButton(new Company((int) $dataRowRaw['company_id']), ['style' => 'height: 40px;']);

        return $dataRowRaw;
    }

    public function getAssignedCredentials(): array
    {
        $crdHlpr = new \MultiFlexi\RunTplCreds();

        return $crdHlpr->getCredentialsForRuntemplate($this->getMyKey())->fetchAll();
    }

    /**
     * @return array<string>
     */
    public function getRequirements(): array
    {
        return $this->getApplication()->getRequirements();
    }

    public function getEnvironment(): ConfigFields
    {
        $runTemplateFields = new ConfigFields(sprintf(_('RunTemplate #%s Environment'), $this->getMyKey()));

        $runTemplateFields->addFields($this->getRuntemplateEnvironment());

        $runTemplateFields->addFields($this->legacyCredentialsEnvironment());

        $runTemplateFields->addFields($this->credentialsEnvironment());

        return $runTemplateFields;
    }

    public function credentialsEnvironment(): ConfigFields
    {
        $runTemplateCredTypeFields = new ConfigFields(_('RunTemplate CredentialType Values'));

        foreach ($this->getCredentialsAssigned() as $requirement => $credentialData) {
            $credentor = new Credential($credentialData['credentials_id']);
            $runTemplateCredTypeFields->addFields($credentor->query());
        }

        return $runTemplateCredTypeFields;
    }

    public function getCredentialsAssigned(): array
    {
        $credentor = new RunTplCreds();

        return $credentor->listingQuery()->select(['credentials.name AS credential_name', 'credential_type.name AS credential_type_name', 'credential_type.class AS credential_type_class', 'credential_type.uuid AS credential_type_uuid', 'credential_type.logo AS credential_type_logo'])->where('runtemplate_id', $this->getMyKey())->leftJoin('credentials ON credentials.id = runtplcreds.credentials_id')->leftJoin('credential_type ON credential_type.id = credentials.credential_type_id')->fetchAll('credential_type_class');
    }
}
