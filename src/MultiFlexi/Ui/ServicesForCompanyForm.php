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

use Ease\TWB4\Form;
use Ease\TWB4\Part;
use MultiFlexi\Application;
use MultiFlexi\Company;
use MultiFlexi\RunTemplate;

/**
 * Description of ServicesForCompanyForm.
 *
 * @author vitex
 */
class ServicesForCompanyForm extends Form
{
    /**
     * Assign Services for company.
     *
     * @param Company $company
     * @param array   $tagProperties
     */
    public function __construct($company, $tagProperties = [])
    {
        $companyID = $company->getMyKey();

        $serverCompanyInfo = (new Company())->listingQuery()->where('company.id', $companyID)->select('servers.type')->leftJoin('servers ON servers.id = company.server')->fetch();
        $apper = new Application();
        $platformApps = $apper->getAvailbleApps($serverCompanyInfo['type'])->orderBy('name');
        // (new Application())->listingQuery()->select('id AS app_id')->select('name AS app_name')->where('enabled', 1)->fetchAll();

        $glue = new RunTemplate();
        $assigned = $glue->getActiveRunTemplatesForCompany($companyID);
        parent::__construct($tagProperties);
        $jobber = new \MultiFlexi\Job();
        $appTabs = new \Ease\TWB4\Tabs();

        foreach ($platformApps as $appData) {
            $apper->setData($appData);
            $appData['company_id'] = $companyID;
            $appData['app_id'] = $appData['id'];
            $appData['app_name'] = $appData['name'];

            if (\array_key_exists($appData['id'], $assigned)) {
                $appData['interv'] = $assigned[$appData['id']]['interv'];
                $appData['runtemplateid'] = $assigned[$appData['id']]['id'];
            }

            $appTabs->addTab(new AppLogo($apper, ['style' => 'height: 20px']).'&nbsp;'._($apper->getRecordName()), new AppRow($appData));
        }

        $this->addItem($appTabs);
    }

    public function finalize(): void
    {
        Part::twBootstrapize();
        $this->addJavaScript(<<<'EOD'


$('#
EOD.$this->getTagID().<<<'EOD'
 select').change( function(event, state) {

$.ajax({
   url: 'toggleapp.php',
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
            type: 'POST'
        });
});

EOD);
        parent::finalize();
    }
}
