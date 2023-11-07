<?php

declare(strict_types=1);

/**
 * Multi Flexi - Application Logo
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi\Ui;

use MultiFlexi\Application;
use Ease\Html\ImgTag;

/**
 * Description of AppLogo
 *
 * @author vitex
 */
class AppLogo extends ImgTag
{
    /**
     * Company Logo
     *
     * @param Application $application
     * @param array $properties
     */
    public function __construct(Application $application, array $properties = [])
    {
        parent::__construct(
            empty($application->getDataValue('image')) ? 'images/apps.svg' : $application->getDataValue('image'),
            $application->getDataValue('name'),
            $properties
        );
        $this->addTagClass('img-fluid');
    }
}
