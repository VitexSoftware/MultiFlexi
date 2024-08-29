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
    public $myTable = 'companyapp';
    public ?Company $company;

    /**
     * @param array $options
     */
    public function __construct(?Company $company = null, $options = [])
    {
        parent::__construct(null, $options);
        $this->company = $company;
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

        $allApps = (new \MultiFlexi\Application())->listingQuery()->select(['id', 'name'], true)->fetchAll('id'); // where('enabled', true)->
        $assigned = $this->getAssigned()->fetchAll('app_id');

        foreach ($appIds as $appId) {
            if ($appId && \array_key_exists($appId, $assigned) === false) {
                if ($this->insertToSQL(['company_id' => $companyId, 'app_id' => $appId])) {
                    $this->addStatusMessage(sprintf(_('Application %s was assigned to %s company'), $allApps[$appId]['name'], $this->company->getRecordName()));
                }
            }
        }

        $runTempate = new RunTemplate();

        foreach ($assigned as $appId => $assId) {
            if (array_search($appId, $appIds, true) === false) {
                if ($this->deleteFromSQL(['company_id' => $companyId, 'app_id' => $appId])) {
                    $runTempate->deleteFromSQL(['app_id' => $appId, 'company_id' => $companyId]);
                    $this->addStatusMessage(sprintf(_('Application %s was unassigned from %s company'), $allApps[$appId]['name'], $this->company->getRecordName()));
                }
            }
        }
    }
}
