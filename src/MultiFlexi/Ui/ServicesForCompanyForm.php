<?php

/**
 * Multi Flexi  - Services for Company
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace MultiFlexi\Ui;

use Ease\TWB4\Form;
use Ease\TWB4\Part;
use MultiFlexi\Application;
use MultiFlexi\RunTemplate;
use MultiFlexi\Company;

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

        $serverCompanyInfo = (new Company())->listingQuery()->where('company.id', $companyID)->select('servers.type')->leftJoin('servers ON servers.id = company.server')->fetch();

        $platformApps = (new Application())->getAvailbleApps($serverCompanyInfo['type'])->orderBy('name');
        //(new Application())->listingQuery()->select('id AS app_id')->select('name AS app_name')->where('enabled', 1)->fetchAll();

        $glue = new RunTemplate();
        $assigned = $glue->getPeriodAppsForCompany($companyID);
        parent::__construct($tagProperties);
        $jobber = new \MultiFlexi\Job();
        $appTabs = new \Ease\TWB4\Tabs();
        foreach ($platformApps as $appData) {
            $appData['company_id'] = $companyID;
            $appData['app_id'] = $appData['id'];
            $appData['app_name'] = $appData['name'];
            if (array_key_exists($appData['id'], $assigned)) {
                $appData['interv'] = $assigned[$appData['id']]['interv'];
                $appData['runtemplateid'] = $assigned[$appData['id']]['id'];
            }
            $appTabs->addTab($appData['name'] ? _($appData['name']) : 'n/a?!', new AppRow($appData));
        }
        $this->addItem($appTabs);
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
