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

namespace AbraFlexi\MultiFlexi\Ui;

use \AbraFlexi\MultiFlexi\Application,
    \Ease\Html\ImgTag;

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
        parent::__construct(empty( $application->getDataValue('image')) ? 'images/apps.svg' :  $application->getDataValue('image'), $application->getDataValue('nazev'), $properties);
        $this->addTagClass('img-fluid');
    }
}