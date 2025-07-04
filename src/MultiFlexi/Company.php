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
 * MultiFlexi - Company Management Class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2025 VitexSoftware
 */
class Company extends \MultiFlexi\Engine
{
    use \Ease\SQL\Orm;
    public string $keyword = 'company';
    public string $lastModifiedColumn = 'DatUpdate';
    public int $abraflexiId;

    public function __construct($identifier = null, $options = [])
    {
        $this->myTable = 'company';
        $this->nameColumn = 'name';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatUpdate';
        parent::__construct($identifier, $options);
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    public function __sleep(): array
    {
        $this->pdo = null;

        return ['data', 'objectName', 'myTable', 'nameColumn', 'createColumn', 'lastModifiedColumn'];
    }

    /**
     * Prepare data for save.
     */
    #[\Override]
    public function takeData(array $data): int
    {
        if (isset($data['enabled'])) {
            $data['enabled'] = true;
        } else {
            $data['enabled'] = false;
        }

        if (\array_key_exists('slug', $data) && empty($data['slug'])) {
            unset($data['slug']);
        }

        if (\array_key_exists('customer', $data) && empty($data['customer'])) {
            unset($data['customer']);
        }

        unset($data['class']);

        if (!\array_key_exists('logo', $data) && \array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir().'/'.basename($_FILES['imageraw']['name']);

            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['logo'] = 'data:'.mime_content_type($uploadfile).';base64,'.base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        }

        //            if(empty($this->getDataValue('logo'))){
        //                $data['logo'] = 'data:image/svg+xml;base64,' . base64_encode(\AbraFlexi\ui\CompanyLogo::$none);
        //            }

        return parent::takeData($data);
    }

    /**
     * Get Current record name.
     *
     * @return string
     */
    public function getRecordName()
    {
        return $this->getDataValue('name');
    }

    /**
     * Link to record's page.
     *
     * @return string
     */
    public function getLink()
    {
        return $this->keyword.'.php?id='.$this->getMyKey();
    }

    /**
     * Company Environment.
     */
    public function getEnvironment(): ConfigFields
    {
        $companyEnv = new ConfigFields();

        $helper = new CompanyEnv($this->getMyKey());

        if ($helper->getData()) {
            foreach ($helper->getData() as $key => $value) {
                $field = new ConfigField($key, 'string', $key, '');
                $field->setValue($value)->setSource(serialize($this));
                $companyEnv->addField($field);
            }
        }

        return $companyEnv;
    }
}
