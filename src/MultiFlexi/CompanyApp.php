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

/**
 * Description of CompanyApp.
 *
 * @author vitex
 */
class CompanyApp extends Engine
{
    public ?Company $company;
    public ?Application $app;

    /**
     * @param array $options
     */
    public function __construct(?Company $company = null, $options = [])
    {
        $this->myTable = 'companyapp';
        parent::__construct(null, $options);
        $this->company = $company;
    }

    public function setApp(Application $application): self
    {
        $this->app = $application;

        return $this;
    }

    public function getAssigned()
    {
        return $this->getAll()->where('company_id', $this->company->getMyKey());
    }

    public function getAll()
    {
        return $this->listingQuery()->select('app_id', true);
    }

    /**
     * (un)assign App with Company.
     *
     * @param array<int> $appIds
     */
    public function assignApps($appIds): void
    {
        $companyId = $this->company->getMyKey();
        $runTempate = new RunTemplate();

        $allApps = (new \MultiFlexi\Application())->listingQuery()->select(['id', 'name'], true)->fetchAll('id'); // where('enabled', true)->
        $assigned = $this->getAssigned()->fetchAll('app_id');

        foreach ($appIds as $appId) {
            if ($appId && \array_key_exists($appId, $assigned) === false) {
                if ($this->insertToSQL(['company_id' => $companyId, 'app_id' => $appId])) {
                    $this->addStatusMessage(sprintf(_('Application %s was assigned to %s company'), $allApps[$appId]['name'], $this->company->getRecordName()));
                    $runTempate->dataReset();
                    $runTempate->setDataValue('app_id', $appId);
                    $runTempate->setDataValue('company_id', $companyId);
                    $runTempate->setDataValue('interv', 'n');
                    $runTempate->setDataValue('name', $allApps[$appId]['name']);
                    $runTempate->insertToSQL();
                }
            }
        }

        foreach ($assigned as $appId => $assId) {
            if (array_search($appId, $appIds, true) === false) {
                $appRuntmps = $runTempate->listingQuery()->where('company_id', $companyId)->where('app_id', $appId);

                if ($this->deleteFromSQL(['company_id' => $companyId, 'app_id' => $appId])) {
                    foreach ($appRuntmps as $runtemplateData) {
                        $rt2ac = $this->getFluentPDO()->deleteFrom('actionconfig')->where('runtemplate_id', $runtemplateData['id'])->execute();

                        if ($rt2ac !== 0) {
                            $this->addStatusMessage(sprintf(_('%s Action Config removal'), $runtemplateData['name']), null === $rt2ac ? 'error' : 'success');
                        }
                    }

                    $runTempate->deleteFromSQL(['app_id' => $appId, 'company_id' => $companyId]);
                    $this->addStatusMessage(sprintf(_('Application %s was unassigned from %s company'), $allApps[$appId]['name'], $this->company->getRecordName()));
                }
            }
        }
    }

    public function getApplication(): Application
    {
        return $this->app;
    }

    public function getCompany(): Company
    {
        return $this->company;
    }
}
