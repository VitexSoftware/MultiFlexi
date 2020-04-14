<?php

/**
 * Multi FlexiBee Setup  - Services for Company
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2015-2020 Vitex Software
 */

namespace FlexiPeeHP\MultiSetup\Ui;

/**
 * Description of ServicesForCompanyForm
 *
 * @author vitex
 */
class ServicesForCompanyForm extends \Ease\TWB4\Form {

    /**
     * 
     * @param \FlexiPeeHP\MultiSetup\Company $company
     * @param array $tagProperties
     */
    public function __construct($company, $tagProperties = array()) {
        $companyID = $company->getMyKey();

        $apps = (new \FlexiPeeHP\MultiSetup\Application())->getAll();
        $glue = new \FlexiPeeHP\MultiSetup\AppToCompany();

        $assigned = $glue->getColumnsFromSQL(['app_id'], ['company_id' => $companyID], 'id', 'app_id');
        parent::__construct($tagProperties);

        foreach ($apps as $appData) {
            $code = $appData['id'];
            $twbsw = $this->addInput(
                    new \Ease\TWB4\Widgets\Toggle($code, array_key_exists($code, $assigned), 1, [
                        'data-company' => $companyID,
                        'data-app' => $code,
                        'data-on' => _('enabled'),
                        'data-off' => _('disabled'),
                        'data-onstyle' => 'success',
                        'data-offstyle' => 'outline-dark',
                        'labelWidth' => 10, 'handleWidth' => 200]),
                    new \Ease\Html\ImgTag($appData['image'], $appData['nazev'], ['height' => '30']) . '&nbsp;' . $appData['nazev'] . '&nbsp;'
            );
        }
    }

    public function finalize() {
        \Ease\TWB4\Part::twBootstrapize();
        $this->addJavaScript('

$(\'#' . $this->getTagID() . ' input\').change( function(event, state) {

$.ajax({
   url: \'toggleapp.php\',
        data: {
                app: $(this).attr("data-app"),
                company: $(this).attr("data-company"),
                state: $(this).prop("checked")
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
