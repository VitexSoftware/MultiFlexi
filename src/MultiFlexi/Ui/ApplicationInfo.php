<?php

/**
 * Multi Flexi - Application Info Panel
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

declare(strict_types=1);

namespace MultiFlexi\Ui;

use MultiFlexi\Application;
use MultiFlexi\Company;
use Ease\TWB4\Panel;

/**
 * Description of ApplicationInfo
 *
 * @author vitex
 */
class ApplicationInfo extends Panel
{
    /**
     * Application Info panel
     *
     * @param Application $application
     * @param Company $company
     */
    public function __construct(Application $application, Company $company)
    {
        parent::__construct($this->headerRow($application), 'info', 'body', 'footer');
    }


    /**
     * logo, name and caption in one TWB Row div
     *
     * @param Application $application
     *
     * @return \Ease\TWB4\Row
     */
    public function headerRow($application)
    {
        $headerRow = new \Ease\TWB4\Row();
        $headerRow->addColumn(2, new AppLogo($application));
        $headerRow->addColumn(4, new \Ease\Html\H3Tag($application->getDataValue('nazev')));
        $headerRow->addColumn(4, $application->getDataValue('popis'));
        return $headerRow;
    }
}
