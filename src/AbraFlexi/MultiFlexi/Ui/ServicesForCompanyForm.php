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

        $assigned = $glue->getColumnsFromSQL(['app_id', 'interv', 'id'], ['company_id' => $companyID], 'id', 'app_id');
        parent::__construct($tagProperties);

        foreach ($apps as $appData) {
            $code = $appData['id'];

            $appRow = new Row();
            $appRow->setTagProperty('style', 'border-bottom: 1px solid #bdbdbd; padding: 5px');

            $appRow->addColumn(2, new ATag('app.php?id=' . $code, new ImgTag($appData['image'], $appData['nazev'], ['class' => 'img-fluid'])));

            $intervalChooser = new IntervalChooser($code . '_interval', array_key_exists($code, $assigned) ? $assigned[$code]['interv'] : 'n', ['id' => $code . '_interval', 'data-company' => $companyID, 'checked' => 'true', 'data-app' => $code]);
            $launchButton = new \Ease\Html\DivTag(new LaunchButton($assigned[$code]['id']));

            $appRow->addColumn(4, new FormGroup('<strong>' . $appData['nazev'] . '</strong> ', $intervalChooser))->addItem($launchButton);

            $appRow->addColumn(6, [new ConfiguredFieldBadges($companyID, $code)]);

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
