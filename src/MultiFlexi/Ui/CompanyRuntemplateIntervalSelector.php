<?php

declare(strict_types=1);

/**
 * Multi Flexi - Select From Company applications not used in any interval
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Description of CompanyAppIntervalSelection
 *
 * @author vitex
 */
class CompanyRuntemplateIntervalSelector extends CompanyAppSelector
{
    private $intervalCode;

    /**
     * 
     * @param type $company
     * @param type $identifier
     * @param type $enabled
     * @param type $optionsPage
     */
    public function __construct($company, $identifier = null, $enabled = [], $optionsPage = 'apps.php')
    {
        $this->intervalCode = \MultiFlexi\Job::intervalToCode($identifier);
        parent::__construct($company, $identifier, $enabled, $optionsPage);
    }

    public function availbleApps()
    {
        $runTemplate = new \MultiFlexi\RunTemplate();
        $companyRuntemplates = $runTemplate->getCompanyTemplates($this->companyId)->fetchAll('id');
        foreach ($companyRuntemplates as $id => $companyRuntemplate) {
            if (empty($companyRuntemplate['name'])){
                $companyRuntemplates[$id]['name'] = strval($id).' '.$companyRuntemplate['app_name'];
            }
            if ($companyRuntemplate['interv'] != 'n') {
                $companyRuntemplates[$id]['disabled'] = 1;
            }
            unset($companyRuntemplates[$id]['success']);
            unset($companyRuntemplates[$id]['fail']);
        }
        return array_values($companyRuntemplates);
    }
}
