<?php

declare(strict_types=1);

/**
 * Multi Flexi -
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

/**
 *
 *
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Application,
    \MultiFlexi\Company,
    \Ease\TWB4\Panel;

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
