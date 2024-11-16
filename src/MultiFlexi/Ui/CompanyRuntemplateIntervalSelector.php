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

/**
 * Description of CompanyAppIntervalSelection.
 *
 * @author vitex
 */
class CompanyRuntemplateIntervalSelector extends CompanyAppSelector
{
    private $intervalCode;

    /**
     * @param type $company
     * @param type $identifier
     * @param type $enabled
     * @param type $optionsPage
     */
    public function __construct($company, $identifier = null, $enabled = [], $optionsPage = 'apps.php')
    {
        $this->intervalCode = \MultiFlexi\RunTemplate::intervalToCode($identifier);
        parent::__construct($company, $identifier, $enabled, $optionsPage);
    }

    public function availbleApps()
    {
        $runTemplate = new \MultiFlexi\RunTemplate();
        $companyRuntemplates = $runTemplate->getCompanyTemplates($this->companyId)->fetchAll('id');

        foreach ($companyRuntemplates as $id => $companyRuntemplate) {
            if (empty($companyRuntemplate['name'])) {
                $companyRuntemplates[$id]['name'] = (string) $id.' '.$companyRuntemplate['app_name'];
            }

            if ($companyRuntemplate['interv'] !== 'n') {
                $companyRuntemplates[$id]['disabled'] = 1;
            }

            unset($companyRuntemplates[$id]['success'], $companyRuntemplates[$id]['fail']);
        }

        return array_values($companyRuntemplates);
    }
}
