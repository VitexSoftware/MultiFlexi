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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of AppJobsTable.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class AppJobsTable extends \Ease\TWB4\Table
{
    public function __construct(int $appId, int $companyId, $properties = [])
    {
        parent::__construct(null, $properties);
        $jobs = (new \MultiFlexi\Job())->listingQuery()->select(['job.id', 'begin', 'exitcode', 'launched_by', 'login'], true)->leftJoin('user ON user.id = job.launched_by')->where('company_id', $companyId)->where('app_id', $appId)->limit(10)->orderBy('job.id DESC')->fetchAll();
        $this->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher')]);

        foreach ($jobs as $job) {
            $job['id'] = new \Ease\Html\ATag('job.php?id='.$job['id'], $job['id']);

            if (empty($job['begin'])) {
                $job['begin'] = _('Not launched yet');
            } else {
                $job['begin'] = [$job['begin'], ' ', new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))];
            }

            $job['exitcode'] = new \MultiFlexi\Ui\ExitCode($job['exitcode']);
            $job['launched_by'] = $job['launched_by'] ? new \Ease\Html\ATag('user.php?id='.$job['launched_by'], $job['login']) : _('Timer');
            unset($job['login']);
            $this->addRowColumns($job);
        }
    }
}
