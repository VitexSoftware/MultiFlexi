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
class CompanyAppIntervalSelector extends CompanyAppSelector
{
    private $intervalCode;

    public function __construct($company, $identifier = null, $enabled = [])
    {
        $this->intervalCode = \MultiFlexi\Job::intervalToCode($identifier);
        parent::__construct($company, $identifier, $enabled);
    }

    public function availbleApps()
    {
        $companyApps = parent::availbleApps();
        $runTemplate = new \MultiFlexi\RunTemplate();
        $currentAppAssigned = $runTemplate->getCompanyTemplates($this->companyId)->fetchAll('app_id');
        foreach ($companyApps as $id => $companyApp) {
            if (array_key_exists($companyApp['id'], $currentAppAssigned)) {
                $companyApp;
                $companyApps[$id]['disabled'] = 1;
            }
        }
        return $companyApps;
    }
}
