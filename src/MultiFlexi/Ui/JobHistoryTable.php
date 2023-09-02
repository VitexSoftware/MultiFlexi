<?php

declare(strict_types=1);
/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */
/**
 * 
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of JobHistory
 *
 * @author vitex
 */
class JobHistoryTable extends \Ease\TWB4\Table
{

    /**
     * Job History presented as table
     * 
     * @param mixed $content
     * @param array $properties
     */
    public function __construct($content = null, $properties = [])
    {
        parent::__construct($content, $properties);
        $jobber = new \MultiFlexi\Job();
        $jobs = $jobber->listingQuery()->select(['apps.nazev AS appname', 'apps.image AS appimage', 'job.id', 'begin', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id',  'job.company_id', 'company.nazev' ], true)->leftJoin('apps ON apps.id = job.app_id')->leftJoin('user ON user.id = job.launched_by')->limit(50)->where('begin IS NOT NULL')->orderBy('job.id DESC')->fetchAll();
        $this->addRowHeaderColumns([_('Application'), _('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher'),_('Company')]);
        foreach ($jobs as $job) {
            $job['appimage'] = new \Ease\Html\ATag('app.php?id=' . $job['app_id'], [new \Ease\TWB4\Badge('light', [new \Ease\Html\ImgTag($job['appimage'], $job['appname'], ['height' => 30, 'title' => $job['appname']]), '&nbsp;', $job['appname']])]);
            unset($job['appname']);
            unset($job['app_id']);
            $job['id'] = new \Ease\Html\ATag('job.php?id=' . $job['id'], new \Ease\TWB4\Badge('info', $job['id']));
            $job['begin'] = [$job['begin'], '<br>', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
            $job['exitcode'] = new ExitCode($job['exitcode']);
            $job['launched_by'] = $job['launched_by'] ? new \Ease\Html\ATag('user.php?id=' . $job['launched_by'], new \Ease\TWB4\Badge('info', $job['login'])) : _('Timer');
            unset($job['login']);
            $job['company_id'] = new \Ease\Html\ATag('company.php?id=' . $job['company_id'], $job['nazev']);
            unset($job['nazev']);
            
            $this->addRowColumns($job);
        }
    }
}
