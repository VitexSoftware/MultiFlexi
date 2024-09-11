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

namespace MultiFlexi\Pohoda;

/**
 * MultiFlexi - Pohoda Company.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

/**
 * Description of Company.
 *
 * @author vitex
 */
class Company extends \MultiFlexi\Company implements \MultiFlexi\platformCompany
{
    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'company';

        parent::__construct($identifier, $options);
    }

    public function getServerEnvironment()
    {
        $server = new Server($this->getDataValue('server'));

        return $server->getEnvironment();
    }

    /**
     * Company Environment with Pohoda Specific values.
     *
     * @return array
     */
    public function getEnvironment(): array
    {
        $companyEnvironment = $this->getServerEnvironment();
        $companyEnvironment['POHODA_ICO']['value'] = $this->getDataValue('ic');
        $companyEnvironment['POHODA_URL']['value'] = $companyEnvironment['POHODA_URL']['value'].':'.$this->getDataValue('company');

        return array_merge($companyEnvironment, parent::getEnvironment());
    }
}
