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

use Ease\Html\ImgTag;
use MultiFlexi\Application;

/**
 * Description of AppLogo.
 *
 * @author vitex
 */
class AppLogo extends ImgTag
{
    /**
     * Company Logo.
     */
    public function __construct(Application $application, array $properties = [])
    {
        parent::__construct(
            empty($application->getDataValue('image')) ? 'images/apps.svg' : $application->getDataValue('image'),
            $application->getDataValue('name'),
            $properties,
        );
        $this->addTagClass('img-fluid');
    }
}
