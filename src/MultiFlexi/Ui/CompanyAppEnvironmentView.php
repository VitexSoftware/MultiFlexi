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

/**
 * @author     Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

namespace MultiFlexi\Ui;

/**
 * Show Full environment for Application.
 *
 * @author vitex
 *
 * @no-named-arguments
 */
class CompanyAppEnvironmentView extends EnvironmentView
{
    public function __construct(int $runTemplateId, $properties = [])
    {
        $appToCompany = new \MultiFlexi\RunTemplate($runTemplateId);
        parent::__construct($appToCompany->getAppEnvironment(), $properties);
    }
}
