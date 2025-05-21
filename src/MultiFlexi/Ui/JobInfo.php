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
class JobInfo extends \Ease\Html\DivTag
{
    public function __construct(\MultiFlexi\Job $job, $properties = [])
    {
        $executorClass = '\\MultiFlexi\\Executor\\'.$job->getDataValue('executor');
        $executorImage = new ExecutorImage($job->getDataValue('executor'), ['height' => 20]);

        $jobInfoRow = new \Ease\TWB4\Row();
        $jobInfoRow->addColumn(1, [_('Exitcode').'<br>', new ExitCode($job->getDataValue('exitcode'), ['style' => 'font-size: 2.0em; font-family: monospace;'])]);
        $jobInfoRow->addColumn(4, [_('Commandline').'<br>', $job->getDataValue('command'), '<br>', $job->application->getRecordName().' v.:'.$job->getDataValue('app_version')]);
        $jobInfoRow->addColumn(2, [_('Scheduled').'<br>',
            $job->getDataValue('schedule') ? $job->getDataValue('schedule').'<br>'.new \Ease\Html\Widgets\LiveAge(new \DateTime($job->getDataValue('schedule'))) : '', '<br>', $executorImage, _('Executor').' '.$executorClass::name()]);
        $jobInfoRow->addColumn(2, [_('Begin').'<br>', [
            $job->getDataValue('begin'),
            '&nbsp;',
            $job->getDataValue('begin') ? new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job->getDataValue('begin')))) : _('Not Yet Started')],
        ]);
        $jobInfoRow->addColumn(2, [_('End').'<br>', [
            $job->getDataValue('end'),
            '&nbsp;',
            $job->getDataValue('end') ? new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job->getDataValue('end')))) : _('Not Yet Ended')],
        ]);

        //        $jobInfoRow->addColumn(1, [_('Commandline').'<br>', $job->getDataValue('command')]);

        $launcher = new \MultiFlexi\User($job->getDataValue('launched_by'));

        $jobInfoRow->addColumn(1, [_('Launched by').'<br>', $launcher->getMyKey() ? new \Ease\Html\ATag('user.php?id='.$launcher->getMyKey(), new \Ease\TWB4\Badge('info', $launcher->getUserLogin())) : _('Timer')]);

        parent::__construct($jobInfoRow, $properties);

        $jobTabs = new \Ease\TWB4\Tabs();

        $jobTabs->addTab('🏁 '._('Job').' <span class="badge badge-primary">'.$job->getMyKey().'</span>', '');

        //        $scheduler = new \MultiFlexi\Scheduler();
        //        $scheduled = $scheduler->listingQuery()->where('job', $job->getMyKey())->fetch();

        $envTabs = new \Ease\TWB4\Tabs();
        $envTabs->addTab(_('Overview'), new EnvironmentView($job->getEnv()));
        $envTabs->addTab(_('export .env'), new JobDotEnv($job));

        $jobTabs->addTab(_('Environment').' <span class="badge badge-info">'.\count($job->getEnv()->getEnvArray()).'</span>', [$jobInfoRow, $envTabs]);

        $this->addItem($jobTabs);
    }
}
