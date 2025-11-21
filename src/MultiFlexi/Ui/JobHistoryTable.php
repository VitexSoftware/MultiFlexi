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
 * Description of JobHistory.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class JobHistoryTable extends \Ease\TWB4\Table
{
    public \MultiFlexi\Job $jobber;
    public int $limit = 50;
    public bool $showIcon = true;
    public bool $showCompany = true;

    /**
     * Job History presented as table.
     *
     * @param mixed                 $content
     * @param array<string, string> $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct($content, $properties);
        $this->jobber = new \MultiFlexi\Job();

        $headings = [];

        if ($this->showIcon) {
            $headings[] = _('Application');
        }

        $headings[] = _('Exit Code').'/'._('Job ID');
        $headings[] = _('Launch time');
        $headings[] = _('Launcher');

        if ($this->showCompany) {
            $headings[] = _('Company');
        }

        $this->addRowHeaderColumns($headings);
    }

    public function getJobs()
    {
        return $this->jobber->listingQuery()->
                        select(['apps.name AS appname', 'apps.uuid', 'job.id', 'begin', 'exitcode', 'launched_by', 'user.login', 'user.enabled AS user_enabled', 'user.firstname', 'user.lastname', 'job.app_id AS app_id', 'job.executor', 'job.company_id', 'company.name', 'company.logo', 'schedule', 'schedule_type'], true)
                            ->leftJoin('apps ON apps.id = job.app_id')
                            ->leftJoin('user ON user.id = job.launched_by')
                            ->limit($this->limit)
                            ->orderBy('job.id DESC');
    }

    public function finalize(): void
    {
        $company = new \MultiFlexi\Company();

        foreach ($this->getJobs() as $job) {
            $exitCode = $job['exitcode'];
            $company->setDataValue('logo', $job['logo']);
            $company->setDataValue('name', $job['name']);

            if ($this->showIcon) {
                $job['uuid'] = new \Ease\Html\ATag('app.php?id='.$job['app_id'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag(empty($job['appimage']) ? 'appimage.php?uuid='.$job['uuid'] : $job['appimage'], _($job['appname']), ['height' => 60, 'title' => $job['appname']]), '&nbsp;', _($job['appname'])])]);
            } else {
                unset($job['uuid']);
            }

            $job['id'] = new \Ease\Html\ATag('job.php?id='.$job['id'], [new ExitCode($exitCode, ['style' => 'font-size: 1.0em; font-family: monospace;']), '<br>', new \Ease\TWB4\Badge('info', '🏁 '.$job['id'])], ['title' => _('Job Info')]);
            unset($job['appname'], $job['app_id']);

            if ($job['begin']) {
                $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(\DateTime::createFromFormat('!Y-m-d H:i:s', $job['begin'])))];
            } else {
                $job['begin'] = '⏳'.($job['schedule'] ? new \Ease\Html\DivTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['schedule']))) : '');
            }

            unset($job['exitcode']);

            // User display with type icon
            if ($job['launched_by'] && $job['login']) {
                $isWebUser = (bool) $job['user_enabled'];
                $userIcon = $isWebUser ? '👤' : '🖥️'; // Web user vs CLI/OS user
                $userName = trim($job['firstname'].' '.$job['lastname']) ?: $job['login'];
                $userBadgeClass = $isWebUser ? 'info' : 'secondary';
                $userBadge = new \Ease\Html\ATag('user.php?id='.$job['launched_by'], new \Ease\TWB4\Badge($userBadgeClass, $userIcon.' '.$userName));
            } else {
                $userBadge = new \Ease\TWB4\Badge('warning', '⏰ '._('Timer'));
            }

            $job['launched_by'] = [
                new ExecutorImage($job['executor'], ['align' => 'right', 'height' => '50px']),
                new \Ease\Html\DivTag($userBadge),
                new \Ease\Html\DivTag($job['schedule']),
                new \Ease\Html\DivTag($job['executor'].' '.$job['schedule_type']),
            ];
            unset($job['executor'], $job['login'], $job['user_enabled'], $job['firstname'], $job['lastname'], $job['schedule']);

            if ($this->showCompany) {
                $job['company_id'] = [new \Ease\Html\ATag('company.php?id='.$job['company_id'], new CompanyLogo($company, ['height' => '60px', 'align' => 'right'])), new \Ease\Html\ATag('company.php?id='.$job['company_id'], $job['name'])];
            } else {
                unset($job['company_id']);
            }

            unset($job['name'], $job['logo'], $job['schedule_type']);

            $this->addRowColumns($job);
        }

        parent::finalize();
    }
}
