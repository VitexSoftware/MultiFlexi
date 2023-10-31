<?php

namespace MultiFlexi;

/**
 * Multi Flexi - Company Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2023 Vitex Software
 */
class Company extends \MultiFlexi\Engine
{

    use \Ease\SQL\Orm;
    public $keyword = 'company';

    public $nameColumn = 'nazev';

    public $createColumn = 'DatCreate';

    public $lastModifiedColumn = 'DatUpdate';

    /**
     * SQL Table we use
     * @var string
     */
    public $myTable = 'company';

    /**
     *
     * @var int
     */
    public $abraflexiId;

    /**
     * MultiFlexi Company
     *
     * @param mixed $init
     * @param array $options
     */
    public function __construct($init = null, $options = [])
    {
        parent::__construct(null, $options);
        $this->setMyTable('company');
        $this->setKeyColumn('id');
        if (is_integer($init)) {
            $this->loadFromSQL($init);
        }
    }

    /**
     * Prepare data for save
     *
     * @param array $data
     *
     * @return int
     */
    public function takeData($data)
    {
        if (isset($data['rw'])) {
            $data['rw'] = true;
        } else {
            $data['rw'] = false;
        }
        if (isset($data['webhook'])) {
            $data['webhook'] = true;
        } else {
            $data['webhook'] = false;
        }
        if (isset($data['enabled'])) {
            $data['enabled'] = true;
        } else {
            $data['enabled'] = false;
        }
        if (isset($data['setup'])) {
            $data['setup'] = true;
        } else {
            $data['setup'] = false;
        }

        if (array_key_exists('company', $data) && empty($data['company'])) {
            unset($data['company']);
        }
        if (array_key_exists('customer', $data) && empty($data['customer'])) {
            unset($data['customer']);
        }

        unset($data['class']);
        $data['logo'] = $this->obtainLogo(intval($data['server']), $data['company']);
        return parent::takeData($data);
    }

    
    public function obtainLogo()
    {
        return $this->getDataValue('logo');
    }
    
    /**
     * Get Current record name
     *
     * @return string
     */
    public function getRecordName()
    {
        return $this->getDataValue('nazev');
    }

    /**
     * Link to record's page
     *
     * @return string
     */
    public function getLink()
    {
        return $this->keyword . '.php?id=' . $this->getMyKey();
    }

    /**
     *
     */
    public function __destruct()
    {
        unset($this->pdo);
    }

    /**
     *
     * @return array
     */
    public function __sleep()
    {
        return ['data', 'objectName', 'evidence'];
    }

    public function getEnvironment()
    {
        return $appEnvironment;
    }
}
