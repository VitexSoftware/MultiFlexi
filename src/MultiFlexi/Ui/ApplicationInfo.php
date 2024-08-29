<?php

/**
 * MultiFlexi - Application Info Panel.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

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

use Ease\TWB4\Panel;
use MultiFlexi\Application;
use MultiFlexi\Company;

/**
 * Description of ApplicationInfo.
 *
 * @author vitex
 */
class ApplicationInfo extends Panel
{
    /**
     * Application Info panel.
     */
    public function __construct(Application $application, Company $company)
    {
        parent::__construct($this->headerRow($application), 'info', 'body', 'footer');
    }

    /**
     * logo, name and caption in one TWB Row div.
     *
     * @param Application $application
     *
     * @return \Ease\TWB4\Row
     */
    public function headerRow($application)
    {
        $headerRow = new \Ease\TWB4\Row();
        $headerRow->addColumn(2, new AppLogo($application));
        $headerRow->addColumn(4, new \Ease\Html\H3Tag($application->getDataValue('name')));
        $headerRow->addColumn(4, $application->getDataValue('description'));

        return $headerRow;
    }
}
