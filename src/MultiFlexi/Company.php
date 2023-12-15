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

    public $nameColumn = 'name';

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

        if (!array_key_exists('logo', $data) && array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir() . '/' . basename($_FILES['imageraw']['name']);
            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['logo'] = 'data:' . mime_content_type($uploadfile) . ';base64,' . base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        } else {
//            if(empty($this->getDataValue('logo'))){
//                $data['logo'] = 'data:image/svg+xml;base64,' . base64_encode(\AbraFlexi\ui\CompanyLogo::$none);
//            }
        }


        return parent::takeData($data);
    }

    /**
     * Get Current record name
     *
     * @return string
     */
    public function getRecordName()
    {
        return $this->getDataValue('name');
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

    /**
     * Company Environment
     *
     * @return array
     */
    public function getEnvironment()
    {
        return (new CompanyEnv($this->getMyKey()))->getData();
    }
    
    
}
