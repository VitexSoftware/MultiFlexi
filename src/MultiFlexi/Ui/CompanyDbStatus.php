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

namespace MultiFlexi\Ui;

/**
 * Description of DbStatus.
 *
 * @author vitex
 */
class CompanyDbStatus extends \Ease\TWB4\Row
{
    /**
     * Show status of database.
     *
     * @param mixed $company
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
        //        $companies = (string) (new \MultiFlexi\Company())->listingQuery()->count();
        //        $apps = (string) (new \MultiFlexi\Application())->listingQuery()->count();
        //        $assigned = (string) (new \MultiFlexi\RunTemplate())->listingQuery()->count();

        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Apps').'&nbsp;', new \Ease\TWB4\PillBadge('info', $apps)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Periodical').'&nbsp;', new \Ease\TWB4\PillBadge('info', $periodical)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\Html\ButtonTag(
            [_('Jobs Total').'&nbsp;', new \Ease\TWB4\PillBadge('info', $jobs)],
            ['class' => 'btn btn-default', 'type' => 'button'],
        ));
        $this->addColumn(2, new \Ease\TWB4\LinkButton(
            '?showOnly=success&id='.$companyId,
            [_('Success Jobs').'&nbsp;', new \Ease\TWB4\PillBadge('success', $jobsSuccess)],
            'success',
        ));
        $this->addColumn(2, new \Ease\TWB4\LinkButton(
            '?showOnly=failed&id='.$companyId,
            [_('Failed Jobs').'&nbsp;', new \Ease\TWB4\PillBadge('danger', $jobs - $jobsSuccess - $jobsUnfinished)],
            'danger',
        ));
        $this->addColumn(2, new \Ease\TWB4\LinkButton(
            '?showOnly=unfinished&id='.$companyId,
            [_('Unfinished Jobs').'&nbsp;', new \Ease\TWB4\PillBadge('warning', $jobsUnfinished)],
            'warning',
        ));
        //        $this->addColumn(2, new \Ease\Html\ButtonTag(
        //            [_('Customers') . '&nbsp;', new \Ease\TWB4\PillBadge('info', $customers)],
        //            ['class' => 'btn btn-default', 'type' => 'button']
        //        ));
        //        $this->addColumn(2, new \Ease\Html\ButtonTag(
        //            [_('Companies') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $companies)],
        //            ['class' => 'btn btn-default', 'type' => 'button']
        //        ));
        //        $this->addColumn(2, new \Ease\Html\ButtonTag(
        //            [_('Assigned') . '&nbsp;', new \Ease\TWB4\PillBadge('success', $assigned)],
        //            ['class' => 'btn btn-default', 'type' => 'button']
        //        ));
    }
}
