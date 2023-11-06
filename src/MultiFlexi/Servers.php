<?php

namespace MultiFlexi;

/**
 * Multi Flexi - Instance Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */
class Servers extends DBEngine
{
    public $keyword = 'server';

    /**
     * Column with name of record
     * @var string
     */
    public $nameColumn = 'name';

    /**
     * We Work With Table
     * @var string
     */
    public $myTable = 'servers';

    /**
     * Column with record create time
     * @var string
     */
    public $createColumn = 'DatCreate';

    /**
     * Column with last record upadate time
     * @var string
     */
    public $modifiedColumn = 'DatSave';

    /**
     * Filter Input data
     *
     * @param array $data
     * @return int data taken count
     */
    public function takeData($data)
    {
        unset($data['class']);
        if (array_key_exists('id', $data)) {
            if (is_null($data['id'])) {
                unset($data['id']);
            } else {
                $data['id'] = intval($data['id']);
            }
        }
        $result = parent::takeData($data);
        if (array_key_exists('name', $data) && !strlen($data['name'])) {
            $this->addStatusMessage(
                _('Instance name cannot be empty'),
                'warning'
            );
            $result = false;
        }
        if (array_key_exists('url', $data) && !strlen($data['url'])) {
            $this->addStatusMessage(
                _('Server API URL cannot be empty'),
                'warning'
            );
            $result = false;
        }
        
        if(($data['type'] == 'Pohoda') && (parse_url($data['url'], PHP_URL_PORT) != null) ){
            $this->addStatusMessage(
                _('Pohoda Server API URL cannot contain port'),
                'warning'
            );
            $result = false;
        }
        
        if (array_key_exists('user', $data) && !strlen($data['user'])) {
            $this->addStatusMessage(_('User name cannot be empty'), 'warning');
            $result = false;
        }
        if (array_key_exists('password', $data) && !strlen($data['password'])) {
            $this->addStatusMessage(
                _('API User password cannot be empty'),
                'warning'
            );
            $result = false;
        }
        if (array_key_exists('company', $data) && !strlen($data['company'])) {
            $this->addStatusMessage(_('Company code cannot be empty'), 'warning');
            $result = false;
        }
        if (substr($data['url'], -1) == '/') {
            $this->addStatusMessage(
                _('Server API URL cannot end with slash'),
                'warning'
            );
            $result = false;
        }

        return $result;
    }

    //    /**
    //     * Get Copany Identification number, establish webhook and save
    //     *
    //     * @param array $data
    //     * @param boolean $searchForID
    //     * @return int result
    //     */
    //    public function saveToSQL($data = null, $searchForID = false)
    //    {
    //        if (is_null($data)) {
    //            $data = $this->getData();
    //        }
    //        if (!isset($data['ic'])) {
    //            $abraflexiData = new \AbraFlexi\Nastaveni(1, $data);
    //            $ic           = $abraflexiData->getDataValue('ic');
    //            if (strlen($ic)) {
    //                $data['ic'] = intval($ic);
    //                $this->addStatusMessage(sprintf(_('Succesfully obtained organisation identification number #%d from AbraFlexi %s'),
    //                        $data['ic'], $data['name']), 'success');
    //            } else {
    //                $this->addStatusMessage(sprintf(_('Cannot obtain organisation identification number for AbraFlexi %s'),
    //                        $data['name']), 'error');
    //            }
    //        }
    //        return parent::saveToSQL($data, $searchForID);
    //    }

    public function prepareRemoteAbraFlexi()
    {
        $companer = new Company(null, $this->getData());
        $settinger = new \AbraFlexi\Nastaveni(
            null,
            array_merge($this->getData(), ['detail' => 'full'])
        );
        //Setup Reminder
        //Setup Invoicer
        //Setup any other apps
        //        $companyData['ic'] = $companyDetails['ic'];
        //        unset($companyData['ic']);
        //        $companyData['name'] = $companyDetails['nazFirmy'];
        //        unset($companyData['name']);
        //        $companer->takeData(array_merge($companyData, $this->getData()));
        //        $prepareResult = $companer->prepareCompany($companer->getDataValue('company'));
        //        $result = $companer->saveToSql(array_merge($companyData,
        //                        $prepareResult));
        //        $companer->addStatusMessage(sprintf(_('Saving Company %s'),
        //                        $companyData['name']), $result ? 'success' : 'error');
    }

    public function setEnvironment()
    {
        $envNames = [
            'ABRAFLEXI_URL' => $this->getDataValue('url'),
            'ABRAFLEXI_LOGIN' => $this->getDataValue('user'),
            'ABRAFLEXI_PASSWORD' => $this->getDataValue('password'),
        ];
        $this->exportEnv($envNames);
    }

    /**
     * Connection info for \AbraFlexi\RO
     *
     * @return array
     */
    public function getConnectionDetails()
    {
        $connectionInfo = $this->getData();
        unset($connectionInfo['id']);
        unset($connectionInfo['DatCreate']);
        unset($connectionInfo['DatSave']);
        return $connectionInfo;
    }
}
