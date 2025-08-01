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

use Ease\Html\ATag;
use Ease\Html\ImgTag;
use Ease\TWB4\FormGroup;

// ? use const __PHPUNIT_ATTR_DRIVER_NAME__;

/**
 * Description of AppRow.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class AppRow extends \Ease\TWB4\Row
{
    /**
     * Application in Company context.
     *
     * @param array                 $appData
     * @param array<string, string> $properties
     */
    public function __construct($appData, $properties = [])
    {
        parent::__construct(null, $properties);
        $appId = $appData['app_id'];
        $app = new \MultiFlexi\Application($appId);
        $appRow = &$this;
        $appRow->setTagProperty('style', 'border-bottom: 1px solid #bdbdbd; padding: 5px');
        $logoColumn = $appRow->addColumn(2, [new \Ease\Html\H2Tag($appData['app_name']), new \Ease\Html\PTag($appData['description']), new ATag('app.php?id='.$appId, new ImgTag($appData['image'], $appData['name'], ['class' => 'img-fluid']))]);
        /* check if app requires upload fields */
        $appFieldsObj = \MultiFlexi\Conffield::getAppConfigs(new \MultiFlexi\Application($appId));
        $appFields = \is_array($appFieldsObj) ? $appFieldsObj : $appFieldsObj->getFields();
        /* if any of fields is upload type then add file input button */
        $uploadFields = array_filter($appFields, static function ($field) {
            return $field instanceof \MultiFlexi\ConfigField && $field->getType() === 'file';
        });

        if (empty($uploadFields)) {
            $intervalChooser = new \MultiFlexi\Ui\IntervalChooser($appId.'_interval', \array_key_exists('interv', $appData) ? $appData['interv'] : 'n', ['id' => $appId.'_interval', 'data-company' => $appData['company_id'], 'checked' => 'true', 'data-app' => $appId]);
        } else {
            $intervalChooser = new \Ease\TWB4\Badge('info', _('Upload field does not allow application scheduling'));
        }

        if (\array_key_exists('runtemplateid', $appData)) {
            $launchButton = new \Ease\Html\DivTag(new \MultiFlexi\Ui\LaunchButton($appData['runtemplateid']));
        } else {
            $launchButton = new \Ease\TWB4\LinkButton('launch.php?app_id='.$appId.'&company_id='.$appData['company_id'], [_('Launch').'&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg btn-block ');
        }

        // use AppLaunchForm instead of LaunchButton
        $launchButton = new AppLaunchForm($app, $appData['company_id']);
        $logoColumn->addItem($launchButton);

        $appConfColumn = $appRow->addColumn(6, new FormGroup(new \Ease\Html\H3Tag(_('Job Config')), $intervalChooser));

        if (\array_key_exists('runtemplateid', $appData)) {
            $appConfColumn->addItem(new \MultiFlexi\Ui\CustomAppEnvironmentView($appData['runtemplateid']));
        } else {
            $cfg = new \MultiFlexi\Conffield();
            $appConfColumn->addItem(new EnvironmentView($cfg->appConfigs($appId)));
        }

        $appConfColumn->addItem(new \Ease\TWB4\LinkButton('custserviceconfig.php?app_id='.$appId.'&amp;company_id='.$appData['company_id'], _('Configure App Environment').' '.new \Ease\Html\ImgTag('images/set.svg', _('Set'), ['height' => '30px']), 'success btn-sm  btn-block'));

        $jobs = (new \MultiFlexi\Job())->listingQuery()->select(['job.id', 'begin', 'exitcode', 'launched_by', 'login'], true)->leftJoin('user ON user.id = job.launched_by')->where('company_id', $appData['company_id'])->where('app_id', $appId)->limit(10)->orderBy('job.id DESC')->fetchAll();
        $jobList = new \Ease\TWB4\Table();
        $jobList->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher')]);

        foreach ($jobs as $job) {
            $job['id'] = new ATag('job.php?id='.$job['id'], '🏁 '.$job['id']);

            if (empty($job['begin'])) {
                $job['begin'] = _('Not launched yet');
            } else {
                $job['begin'] = [$job['begin'], ' ', new \Ease\Html\SmallTag(new \Ease\Html\Widgets\LiveAge(new \DateTime($job['begin'])))];
            }

            $job['exitcode'] = new \MultiFlexi\Ui\ExitCode($job['exitcode']);
            $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id='.$job['launched_by'], $job['login']) : _('Timer');
            unset($job['login']);
            $jobList->addRowColumns($job);
        }

        $historyButton = (new \Ease\TWB4\LinkButton('joblist.php?app_id='.$appId.'&amp;company_id='.$appData['company_id'], _('Job History').' '.new \Ease\Html\ImgTag('images/log.svg', _('Set'), ['height' => '30px']), 'info btn-sm  btn-block'));
        $appRow->addColumn(4, [new \Ease\Html\H3Tag(_('Last 10 jobs')), $jobList, $historyButton]);
    }
}
