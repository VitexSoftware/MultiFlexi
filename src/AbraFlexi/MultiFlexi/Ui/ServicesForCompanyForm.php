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
class ServicesForCompanyForm extends Form {

    /**
     * Assign Services for company
     * 
     * @param Company $company
     * @param array $tagProperties
     */
    public function __construct($company, $tagProperties = array()) {
        $companyID = $company->getMyKey();

        $apps = (new Application())->listingQuery()->where('enabled', 1)->fetchAll();
        $glue = new AppToCompany();

        $assigned = $glue->getAppsForCompany($companyID);
        parent::__construct($tagProperties);

        $jobber = new \AbraFlexi\MultiFlexi\Job();
                
        foreach ($apps as $appData) {
            $code = $appData['id'];

            $appRow = new Row();
            $appRow->setTagProperty('style', 'border-bottom: 1px solid #bdbdbd; padding: 5px');

            $appRow->addColumn(2, new ATag('app.php?id=' . $code, new ImgTag($appData['image'], $appData['nazev'], ['class' => 'img-fluid'])));

            $intervalChooser = new IntervalChooser($code . '_interval', array_key_exists($code, $assigned) ? $assigned[$code]['interv'] : 'n', ['id' => $code . '_interval', 'data-company' => $companyID, 'checked' => 'true', 'data-app' => $code]);

            if (array_key_exists($code, $assigned)) {
                $launchButton = new \Ease\Html\DivTag(new LaunchButton($assigned[$code]['id']));
            } else {
                $launchButton = new \Ease\TWB4\LinkButton('launch.php?app_id='.$code.'&company_id=' . $companyID, [_('Launch') . '&nbsp;&nbsp;', new \Ease\Html\ImgTag('images/rocket.svg', _('Launch'), ['height' => '30px'])], 'warning btn-lg btn-block ');
            }

            $appRow->addColumn(4, new FormGroup('<strong>' . $appData['nazev'] . '</strong> ', $intervalChooser))->addItem($launchButton);

            $jobs = $jobber->listingQuery()->select(['id','begin','exitcode'],true)->where('company_id',$companyID)->where('app_id',$code)->limit(10)->orderBy('job.id DESC')->fetchAll();
            
            $jobList = new \Ease\TWB4\Table();
            $jobList->addRowHeaderColumns([_('Job ID'),_('Launch time'),_('Exit Code')]);
            foreach ($jobs as $job) {
                $job['id'] = new ATag('job.php?id='.$job['id'], $job['id']);
                $jobList->addRowColumns($job);
            }
            
            $appRow->addColumn(6, [new ConfiguredFieldBadges($companyID, $code),$jobList]);

            $this->addItem($appRow);
        }
    }

    /**
     * 
     */
    public function finalize() {
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
