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
 * @copyright  2023-2024 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of JobInfo.
 *
 * @author vitex
 */
class JobInfo extends \Ease\TWB4\Tabs
{
    public function __construct(\MultiFlexi\Job $job, $properties = [])
    {
        parent::__construct(null, $properties);

        $jobInfoRow = new \Ease\TWB4\Row();
        $jobInfoRow->addColumn(1, [_('Exitcode').'<br>', new ExitCode($job->getDataValue('exitcode'), ['style' => 'font-size: 2.0em; font-family: monospace;'])]);
        $jobInfoRow->addColumn(4, [_('Commandline').'<br>', $job->getDataValue('command'), '<br>', $job->application->getRecordName().' v.:'.$job->getDataValue('app_version')]);
        $jobInfoRow->addColumn(2, [_('Scheduled').'<br>', $job->getDataValue('schedule')]);
        $jobInfoRow->addColumn(2, [_('Begin').'<br>', [
            $job->getDataValue('begin'),
            '&nbsp;',
            $job->getDataValue('begin') ? new \Ease\Html\SmallTag(new \Ease\Html\WidgetsLiveAge((new \DateTime($job->getDataValue('begin')))->getTimestamp())) : _('Not Yet Started')],
        ]);
        $jobInfoRow->addColumn(2, [_('End').'<br>', [
            $job->getDataValue('end'),
            '&nbsp;',
            $job->getDataValue('end') ? new \Ease\Html\SmallTag(new \Ease\Html\WidgetsLiveAge((new \DateTime($job->getDataValue('end')))->getTimestamp())) : _('Not Yet Ended')],
        ]);

        //        $jobInfoRow->addColumn(1, [_('Commandline').'<br>', $job->getDataValue('command')]);

        $launcher = new \MultiFlexi\User($job->getDataValue('launched_by'));
        $jobInfoRow->addColumn(1, [_('Launched by').'<br>', $launcher->getMyKey() ? new \Ease\Html\ATag('user.php?id='.$launcher->getMyKey(), new \Ease\TWB4\Badge('info', $launcher->getUserLogin())) : _('Timer')]);

        $this->addTab(_('Job').' '.$job->getMyKey(), $jobInfoRow);

        //        $scheduler = new \MultiFlexi\Scheduler();
        //        $scheduled = $scheduler->listingQuery()->where('job', $job->getMyKey())->fetch();

        $this->addTab(_('Environment'), new EnvironmentView($job->getEnv()));
    }
}
