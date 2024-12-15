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

namespace MultiFlexi;

/**
 * Description of CredentialType.
 *
 * @author Vitex <info@vitexsoftware.cz>
 */
class CredentialType extends DBEngine
{
    public function __construct($init = null, $filter = [])
    {
        $this->myTable = 'credential_types';
        parent::__construct(false /* TODO $init */, $filter);
        $formClass = '\\MultiFlexi\\Ui\\Form\\'.$init;

        if (class_exists($formClass)) {
            $this->setDataValue('logo', $formClass::$logo);
            $this->setDataValue('name', $formClass::name());
        }
    }
}
