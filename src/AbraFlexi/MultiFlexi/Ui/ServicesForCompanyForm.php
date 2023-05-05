<?php

/**
 * Multi Flexi  - Services for Company
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi\Ui;

use Ease\Html\ATag;
use Ease\Html\ImgTag;
use Ease\TWB4\Form;
use Ease\TWB4\FormGroup;
use Ease\TWB4\Part;
use Ease\TWB4\Row;
use AbraFlexi\MultiFlexi\Application;
use AbraFlexi\MultiFlexi\AppToCompany;
use AbraFlexi\MultiFlexi\Company;

/**
 * Description of ServicesForCompanyForm
 *
 * @author vitex
 */
class ServicesForCompanyForm extends Form
{

    /**
     * Assign Services for company
     * 
     * @param Company $company
     * @param array $tagProperties
     */
    public function __construct($company, $tagProperties = array())
    {
        $companyID = $company->getMyKey();
        $apps = (new Application())->listingQuery()->where('enabled', 1)->fetchAll();
        $glue = new AppToCompany();
        $assigned = $glue->getAppsForCompany($companyID);
        parent::__construct($tagProperties);
        $jobber = new \AbraFlexi\MultiFlexi\Job();
        foreach ($apps as $appData) {
            $appId = $appData['id'];
            $appRow = new Row();
            $appRow->setTagProperty('style', 'border-bottom: 1px solid #bdbdbd; padding: 5px');
            $logoColumn = $appRow->addColumn(2, [ new \Ease\Html\H2Tag($appData['nazev']), new \Ease\Html\PTag($appData['popis']) , new ATag('app.php?id=' . $appId, new ImgTag($appData['image'], $appData['nazev'], ['class' => 'img-fluid']))]);
            $intervalChooser = new IntervalChooser($appId . '_interval', array_key_exists($appId, $assigned) ? $assigned[$appId]['interv'] : 'n', ['id' => $appId . '_interval', 'data-company' => $companyID, 'checked' => 'true', 'data-app' => $appId]);
            if (array_key_exists($appId, $assigned)) {
                $launchButton = new \Ease\Html\DivTag(new LaunchButton($assigned[$appId]['id']));
            } else {
                $launchButton = new \Ease\TWB4\LinkButton('launch.php?app_id=' . $appId . '&company_id=' . $companyID, [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg btn-block ');
            }
            $logoColumn->addItem($launchButton);
            
            $appConfColumn = $appRow->addColumn(4, new FormGroup(new \Ease\Html\H3Tag('Job Config'), $intervalChooser));
            
            $appConfColumn->addItem(new CustomAppEnvironmentView((int)$assigned[$appId]['id']));
            $appConfColumn->addItem(new \Ease\TWB4\LinkButton('custserviceconfig.php?app_id=' . $appId . '&amp;company_id=' . $companyID , _('Configure App Environment').' '. new \Ease\Html\ImgTag('images/set.svg',_('Set'),['height'=>'30px']) , 'success btn-sm  btn-block'));
            
            $jobs = $jobber->listingQuery()->select(['job.id', 'begin', 'exitcode', 'launched_by', 'login'], true)->leftJoin('user ON user.id = job.launched_by')->where('company_id', $companyID)->where('app_id', $appId)->limit(10)->orderBy('job.id DESC')->fetchAll();
            $jobList = new \Ease\TWB4\Table();
            $jobList->addRowHeaderColumns([_('Job ID'), _('Launch time'), _('Exit Code'), _('Launcher')]);
            foreach ($jobs as $job) {
                $job['id'] = new ATag('job.php?id=' . $job['id'], $job['id']);
                $job['begin'] = [$job['begin'], ' ', new \Ease\Html\SmallTag(new \Ease\ui\LiveAge((new \DateTime($job['begin']))->getTimestamp()))];
                $job['exitcode'] = new ExitCode($job['exitcode']);
                $job['launched_by'] = $job['launched_by'] ? new ATag('user.php?id=' . $job['launched_by'], $job['login']) : _('Timer');
                unset($job['login']);
                $jobList->addRowColumns($job);
            }

            $historyButton = (new \Ease\TWB4\LinkButton('joblist.php?app_id=' . $appId . '&amp;company_id=' . $companyID , _('Job History').' '. new \Ease\Html\ImgTag('images/log.svg',_('Set'),['height'=>'30px']) , 'info btn-sm  btn-block'));
            
            
            $appRow->addColumn(6, [new \Ease\Html\H3Tag(_('Last 10 jobs')), $jobList,$historyButton]);
            $this->addItem($appRow);
        }
    }

    /**
     * 
     */
    public function finalize()
    {
        Part::twBootstrapize();
        $this->addJavaScript('

$(\'#' . $this->getTagID() . ' select\').change( function(event, state) {

$.ajax({
   url: \'toggleapp.php\',
        data: {
                app: $(this).attr("data-app"),
                company: $(this).attr("data-company"),
                interval: $(this).val()
        },
        error: function() {
            console.log("not saved");
        },

        success: function(data) {
            console.log("saved");
        },
            type: \'POST\'
        });
});
');
    }
}
