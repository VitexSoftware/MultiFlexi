<?php

/**
 * Multi Flexi  - Database use overview
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of DbStatus
 *
 * @author vitex
 */
class CompanyDbStatus extends \Ease\TWB4\Row
{
    /**
     * Show status of database
     */
    public function __construct($company)
    {
        parent::__construct();
        $companyId = $company->getMyKey();
        $jobs = (string) (new \MultiFlexi\Job())->listingQuery()->where('company_id', $companyId)->count();
        $jobsSuccess = (string) (new \MultiFlexi\Job())->listingQuery()->where('company_id', $companyId)->where('exitcode', 0)->count();
        $jobsUnfinished = (string) (new \MultiFlexi\Job())->listingQuery()->where('company_id', $companyId)->where('end', null)->count();
        $apps = (new \MultiFlexi\CompanyApp($company))->getAssigned()->count();
        $periodical = (string) (new \MultiFlexi\RunTemplate())->listingQuery()->where('company_id', $companyId)->count();
//        $customers = (string) (new \MultiFlexi\Customer())->listingQuery()->count();
//        $companys = (string) (new \MultiFlexi\Company())->listingQuery()->count();
//        $apps = (string) (new \MultiFlexi\Application())->listingQuery()->count();
//        $assigned = (string) (new \MultiFlexi\RunTemplate())->listingQuery()->count();

        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Apps') . '&nbsp;', new \Ease\TWB4\PillBadge('info', $apps)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Periodical') . '&nbsp;', new \Ease\TWB4\PillBadge('info', $periodical)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Jobs Total') . '&nbsp;', new \Ease\TWB4\PillBadge('info', $jobs)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Success Jobs') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $jobsSuccess)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Failed Jobs') . '&nbsp;', new \Ease\TWB4\PillBadge('danger', $jobs - $jobsSuccess - $jobsUnfinished)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Unfinished Jobs') . '&nbsp;', new \Ease\TWB4\PillBadge('warning', $jobsUnfinished)],
            ['class' => 'btn btn-default', 'type' => 'button']
        ));
//        $this->addColumn(2, new \Ease\Html\ButtonTag(
//            [_('Customers') . '&nbsp;', new \Ease\TWB4\PillBadge('info', $customers)],
//            ['class' => 'btn btn-default', 'type' => 'button']
//        ));
//        $this->addColumn(2, new \Ease\Html\ButtonTag(
//            [_('Companies') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $companys)],
//            ['class' => 'btn btn-default', 'type' => 'button']
//        ));
//        $this->addColumn(2, new \Ease\Html\ButtonTag(
//            [_('Assigned') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $assigned)],
//            ['class' => 'btn btn-default', 'type' => 'button']
//        ));
    }
}
