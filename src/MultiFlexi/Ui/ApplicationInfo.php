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
    public function __construct(Application $application)
    {
        $body = new \Ease\Html\DivTag();
        $body->addItem(new AppLogo($application));

        parent::__construct($this->headerRow($application), 'inverse', $body, new AppLastMonthChart($application));
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

        $appData = new \Ease\Html\DivTag();
        $appData->addItem(new \Ease\Html\H3Tag($application->getDataValue('name')));
        $appData->addItem(new \Ease\Html\PTag($application->getDataValue('description')));
        $appData->addItem(new \Ease\Html\PTag(new \Ease\Html\ATag($application->getDataValue('homepage'), $application->getDataValue('homepage'))));
        $appData->addItem(new \Ease\Html\PTag($application->getDataValue('uuid')));
        $appData->addItem(new \Ease\Html\PTag($application->getDataValue('ociimage')));
        $appData->addItem(new \Ease\Html\PTag($application->getDataValue('version')));
        $appData->addItem(new \Ease\Html\PTag(new RequirementsOverview($application->getRequirements())));

        $headerRow->addColumn(12, $appData);

        return $headerRow;
    }
}
