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
 * MultiFlexi - Instance Management Class.
 *
 * @deprecated since version 1.25
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2023 Vitex Software
 */
class Servers extends DBEngine
{
    public ?string $keyword = 'server';

    /**
     * Column with last record upadate time.
     */
    public ?string $modifiedColumn = 'DatSave';

    public function __construct($init = null, $filter = [])
    {
        $this->myTable = 'servers';
        $this->createColumn = 'DatCreate';
        $this->nameColumn = 'name';
        parent::__construct($init, $filter);
    }

    /**
     * Filter Input data.
     *
     * @return int data taken count
     */
    #[\Override]
    public function takeData(array $data): int
    {
        unset($data['class']);

        if (\array_key_exists('id', $data)) {
            if (null === $data['id']) {
                unset($data['id']);
            } else {
                $data['id'] = (int) $data['id'];
            }
        }

        $result = parent::takeData($data);

        if (\array_key_exists('name', $data) && !\strlen($data['name'])) {
            $this->addStatusMessage(
                _('Instance name cannot be empty'),
                'warning',
            );
            $result = false;
        }

        if (\array_key_exists('url', $data) && !\strlen($data['url'])) {
            $this->addStatusMessage(
                _('Server API URL cannot be empty'),
                'warning',
            );
            $result = false;
        }

        if (($data['type'] === 'Pohoda') && (parse_url($data['url'], \PHP_URL_PORT) !== null)) {
            $this->addStatusMessage(
                _('Pohoda Server API URL cannot contain port'),
                'warning',
            );
            $result = false;
        }

        if (\array_key_exists('user', $data) && !\strlen($data['user'])) {
            $this->addStatusMessage(_('User name cannot be empty'), 'warning');
            $result = false;
        }

        if (\array_key_exists('password', $data) && !\strlen($data['password'])) {
            $this->addStatusMessage(
                _('API User password cannot be empty'),
                'warning',
            );
            $result = false;
        }

        if (\array_key_exists('company', $data) && !\strlen($data['company'])) {
            $this->addStatusMessage(_('Company code cannot be empty'), 'warning');
            $result = false;
        }

        if (substr($data['url'], -1) === '/') {
            $this->addStatusMessage(
                _('Server API URL cannot end with slash'),
                'warning',
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

    public function prepareRemoteAbraFlexi(): void
    {
        $companer = new Company(null, $this->getData());
        $settinger = new \AbraFlexi\Nastaveni(
            null,
            array_merge($this->getData(), ['detail' => 'full']),
        );
        // Setup Reminder
        // Setup Invoicer
        // Setup any other apps
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

    public function setEnvironment(): void
    {
        $envNames = [
            'ABRAFLEXI_URL' => $this->getDataValue('url'),
            'ABRAFLEXI_LOGIN' => $this->getDataValue('user'),
            'ABRAFLEXI_PASSWORD' => $this->getDataValue('password'),
        ];
        $this->exportEnv($envNames);
    }

    /**
     * Connection info for \AbraFlexi\RO.
     *
     * @return array
     */
    public function getConnectionDetails()
    {
        $connectionInfo = $this->getData();
        unset($connectionInfo['id'], $connectionInfo['DatCreate'], $connectionInfo['DatSave']);

        return $connectionInfo;
    }
}
