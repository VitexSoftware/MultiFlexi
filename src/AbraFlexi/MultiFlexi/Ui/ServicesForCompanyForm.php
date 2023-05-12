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
        $allEnabledApps = (new Application())->listingQuery()->select('id AS app_id')->select('nazev AS app_name')->where('enabled', 1)->fetchAll();
        $glue = new AppToCompany();
        $assigned = $glue->getAppsForCompany($companyID);
        parent::__construct($tagProperties);
        $jobber = new \AbraFlexi\MultiFlexi\Job();
        foreach ($allEnabledApps as $appData) {
            $appData['company_id'] = $companyID;
            if(array_key_exists($appData['id'], $assigned)){
                $appData['interv'] = $assigned[$appData['id']]['interv'];
                $appData['appcompanyid'] = $assigned[$appData['id']]['id'];
            }
            $this->addItem(new AppRow($appData));
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
