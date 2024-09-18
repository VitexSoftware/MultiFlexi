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
 * Description of CompanyEnv.
 *
 * @author vitex
 */
class CompanyEnv extends \Ease\SQL\Engine
{
    private ?int $companyID;

    /**
     * @param int   $companyID
     * @param array $options
     */
    public function __construct($companyID = null, $options = [])
    {
        $this->myTable = 'companyenv';
        parent::__construct(null, $options);
        $this->companyID = $companyID;
        $this->loadEnv();
    }

    /**
     * Add Configuration to Company's Environment store.
     *
     * @param string $key   Name of Value to keep
     * @param string $value Value of Configuration
     */
    public function addEnv($key, $value): void
    {
        try {
            if (null !== $this->insertToSQL(['company_id' => $this->companyID, 'keyword' => $key, 'value' => $value])) {
                $this->setDataValue($key, $value);
            }
        } catch (\PDOException $exc) {
            // echo $exc->getTraceAsString();
        }
    }

    public function updateEnv(): void
    {
    }

    public function removeEnv(): void
    {
    }

    public function loadEnv(): void
    {
        foreach ($this->listingQuery()->where('company_id', $this->companyID)->fetchAll() as $companyEnvRow) {
            $this->setDataValue($companyEnvRow['keyword'], $companyEnvRow['value']);
        }
    }

    public function getEnvFields()
    {
        return $this->listingQuery()->where('company_id', $this->companyID)->fetchAll();
    }
}
