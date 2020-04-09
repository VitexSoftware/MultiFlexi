<?php

namespace FlexiPeeHP\MultiSetup;

/**
 * Multi FlexiBee Setup - Instance Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */
class FlexiBees extends \Ease\SQL\Engine {

    public $keyword = 'flexibee';

    /**
     * Column with name of record
     * @var string
     */
    public $nameColumn = 'name';

    /**
     * We Work With Table
     * @var string
     */
    public $myTable = 'flexibees';

    /**
     * Column with record create time
     * @var string
     */
    public $myCreateColumn = 'DatCreate';

    /**
     * Column with last record upadate time
     * @var string
     */
    public $myLastModifiedColumn = 'DatSave';

    /**
     * Filter Input data
     *
     * @param array $data
     * @return int data taken count
     */
    public function takeData($data) {
        unset($data['class']);
        if (isset($data['id'])) {
            if (empty($data['id'])) {
                unset($data['id']);
            } else {
                $data['id'] = intval($data['id']);
            }
        }
        $result = parent::takeData($data);
        if (array_key_exists('name', $data) && !strlen($data['name'])) {
            $this->addStatusMessage(_('Instance name cannot be empty'),
                    'warning');
            $result = false;
        }
        if (array_key_exists('url', $data) && !strlen($data['url'])) {
            $this->addStatusMessage(_('FlexiBee API URL cannot be empty'),
                    'warning');
            $result = false;
        }
        if (array_key_exists('user', $data) && !strlen($data['user'])) {
            $this->addStatusMessage(_('User name cannot be empty'), 'warning');
            $result = false;
        }
        if (array_key_exists('password', $data) && !strlen($data['password'])) {
            $this->addStatusMessage(_('API User password cannot be empty'),
                    'warning');
            $result = false;
        }
        if (array_key_exists('company', $data) && !strlen($data['company'])) {
            $this->addStatusMessage(_('Company code cannot be empty'), 'warning');
            $result = false;
        }

        return $result;
    }

    /**
     * Obtain link to FlexiBee webserver
     *
     * @return string
     */
    function getLink() {
        return $this->getDataValue('url') . '/c/' . $this->getDataValue('company');
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
//            $flexiBeeData = new \FlexiPeeHP\Nastaveni(1, $data);
//            $ic           = $flexiBeeData->getDataValue('ic');
//            if (strlen($ic)) {
//                $data['ic'] = intval($ic);
//                $this->addStatusMessage(sprintf(_('Succesfully obtained organisation identification number #%d from FlexiBee %s'),
//                        $data['ic'], $data['name']), 'success');
//            } else {
//                $this->addStatusMessage(sprintf(_('Cannot obtain organisation identification number for FlexiBee %s'),
//                        $data['name']), 'error');
//            }
//        }
//        return parent::saveToSQL($data, $searchForID);
//    }

    public function prepareRemoteFlexiBee() {
        $companer = new Company(null, $this->getData());
        $settinger = new \FlexiPeeHP\Nastaveni(null,
                array_merge($this->getData(), ['detail' => 'full']));

        //Setup Reminder
        //Setup Invoicer
        //Setup any other apps 
        
        
//        $companyData['ic'] = $companyDetails['ic'];
//        unset($companyData['ic']);
//        $companyData['nazev'] = $companyDetails['nazFirmy'];
//        unset($companyData['name']);
//        $companer->takeData(array_merge($companyData, $this->getData()));
//        $prepareResult = $companer->prepareCompany($companer->getDataValue('company'));
//        $result = $companer->saveToSql(array_merge($companyData,
//                        $prepareResult));
//        $companer->addStatusMessage(sprintf(_('Saving Company %s'),
//                        $companyData['nazev']), $result ? 'success' : 'error');
    }

}
