<?php

declare(strict_types=1);

namespace MultiFlexi\Pohoda;

/**
 * Multi Flexi - Pohoda Company
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

/**
 * Description of Company
 *
 * @author vitex
 */
class Company extends \MultiFlexi\Company implements \MultiFlexi\platformCompany
{
    /**
     * SQL Table we use
     * @var string
     */
    public $myTable = 'company';

    public function getServerEnvironment()
    {
        $server = new Server($this->getDataValue('server'));
        return $server->getEnvironment();
    }

    /**
     * Company Environment with Pohoda Specific values
     *
     * @return array
     */
    public function getEnvironment()
    {
        $companyEnvironment = $this->getServerEnvironment();
        $companyEnvironment['POHODA_ICO'] = $this->getDataValue('ic');
        $companyEnvironment['POHODA_URL'] = $companyEnvironment['POHODA_URL'] . ':' . $this->getDataValue('company');
        return array_merge($companyEnvironment, parent::getEnvironment());
    }
}
