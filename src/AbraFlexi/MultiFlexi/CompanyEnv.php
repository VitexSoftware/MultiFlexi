<?php

/**
 * Multi Flexi - 
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

/**
 * Description of CompanyEnv
 *
 * @author vitex
 */
class CompanyEnv extends \Ease\SQL\Engine {

    public $myTable = 'companyenv';

    /**
     * 
     * @var int
     */
    private $companyID;

    /**
     * 
     * @var array
     */
    public $data = [];


    /**
     * 
     * @param type $companyID
     * @param type $options
     */
    public function __construct($companyID = null, $options = []) {
        parent::__construct(null, $options);
        $this->companyID = $companyID;
        $this->loadEnv();
    }

    public function addEnv($key, $value) {
        try {
            if (!is_null($this->insertToSQL(['company_id' => $this->companyID, 'keyword' => $key, 'value' => $value]))) {
                $this->setDataValue($key, $value);
            }
        } catch (\PDOException $exc) {
            //echo $exc->getTraceAsString();
        }
    }

    public function updateEnv() {
        
    }

    public function removeEnv() {
        
    }

    public function loadEnv() {
        foreach ($this->listingQuery()->where('company_id', $this->companyID)->fetchAll() as $companyEnvRow) {
            $this->setDataValue($companyEnvRow['keyword'], $companyEnvRow['value']);
        }
    }

}
