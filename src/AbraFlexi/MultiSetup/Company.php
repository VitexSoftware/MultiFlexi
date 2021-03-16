<?php

namespace AbraFlexi\MultiSetup;

/**
 * Multi AbraFlexi Setup - Company Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */
class Company extends \AbraFlexi\Company {

    use \Ease\SQL\Orm;

    public $keyword = 'company';
    public $nameColumn = 'nazev';

    public function __construct($init = null, $options = array()) {
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatUpdate';
        parent::__construct(null, $options);
        $this->setMyTable('company');
        $this->setKeyColumn('id');
        if (is_integer($init)) {
            $this->loadFromSQL($init);
            $this->setCompany($this->getDataValue('company'));
        }
    }

    public function prepareCompany($company) {
        $result = ['webhook' => false];
        $this->setCompany($company);
//        $result['labels'] = $this->addAbraFlexiLabel('TaxTorro') && $this->addAbraFlexiLabel('DataMolino');
        if ($this->changesApi(true)) {
            $result['webhook'] = $this->registerWebHook(self::webHookUrl($this->getMyKey()));
        }
        return $result;
    }

    /**
     * WebHook url for Given ID of AbraFlexi instance
     * 
     * @param int $instanceId
     * 
     * @return string URL for WebHook
     */
    public static function webHookUrl($instanceId) {
        $baseUrl = \Ease\Document::phpSelf();
        $urlInfo = parse_url($baseUrl);
        $curFile = basename($urlInfo['path']);
        $webHookUrl = str_replace($curFile,
                'webhook.php?instanceid=' . $instanceId, $baseUrl);
        return $webHookUrl;
    }

    /**
     * Add requied AbraFlexi label to company
     * 
     * @param string $label
     * 
     * @return boolean
     */
    public function addAbraFlexiLabel($label) {
        $result = true;
        $evidenceToVsb = array_flip(\AbraFlexi\Stitek::$vsbToEvidencePath);
        /**
         * @var \AbraFlexi\Stitek Label Object
         */
        $stitek = new \AbraFlexi\Stitek(null, $this->getConnectionOptions());
        /**
         * @see https://demo.abraflexi.eu/c/demo/stitek/properties
         * @var array initial Label contexts
         */
        $stitekData = [
            "kod" => strtoupper($label),
            "nazev" => $label,
            $evidenceToVsb['adresar'] => true,
            $evidenceToVsb['cenik'] => true,
            $evidenceToVsb['faktura-vydana'] => true,
            $evidenceToVsb['faktura-prijata'] => true,
            $evidenceToVsb['objednavka-vydana'] => true,
            $evidenceToVsb['objednavka-prijata'] => true,
        ];

        $stitekID = $stitek->getColumnsFromAbraFlexi('id', $stitekData);

        if (!isset($stitekID[0]['id'])) {
            $stitek->insertToAbraFlexi($stitekData);
            if ($stitek->lastResponseCode == 201) {
                $stitek->addStatusMessage(sprintf(_('label %s created'), $label),
                        'success');
            } else {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * 
     * @param string $hookurl
     */
    public function registerWebHook($hookurl) {
        $format = 'json';
        $hooker = new \AbraFlexi\Hooks(null, $this->getData());
        $hooker->setDataValue('skipUrlTest', 'true');
        $hookResult = $hooker->register($hookurl, $format);
        if ($hookResult) {
            $hooker->addStatusMessage(sprintf(_('Hook %s was registered'),
                            $hookurl), 'success');
            $hookurl = '';
        } else {
            $hooker->addStatusMessage(sprintf(_('Hook %s not registered'),
                            $hookurl), 'warning');
        }
        return (($hooker->lastResponseCode == 201) && ($hooker->lastResponseCode == 200));
    }

    /**
     * Eanble Or disble ChangesAPI
     * 
     * @param boolean $enable requested state
     * 
     * @return boolean
     */
    public function changesApi($enable) {
        $changer = new \AbraFlexi\Changes(null, $this->getData());
        $chapistatus = $changer->getStatus();
//        $globalVersion = $changer->getGlobalVersion();

        if ($enable === true) {
            if ($chapistatus === FALSE) {
                $changer->enable();
                $changer->addStatusMessage(_('ChangesAPI was enabled'),
                        'success');
                $chapistatus = true;
            }
        } else {
            if ($chapistatus === TRUE) {
                $changer->disable();
                $changer->addStatusMessage(_('ChangesAPI was disabled'),
                        'warning');
                $chapistatus = false;
            }
        }
        return $chapistatus;
    }

    /**
     * Prepare data for save
     * 
     * @param array $data
     * 
     * @return array
     */
    public function takeData($data) {
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

        if (array_key_exists('company', $data) && empty($data['company'])) {
            unset($data['company']);
        }
        if (array_key_exists('customer', $data) && empty($data['customer'])) {
            unset($data['customer']);
        }

        unset($data['class']);

        $data['logo'] = $this->obtainLogo(intval($data['abraflexi']),$data['company']);

        return parent::takeData($data);
    }

    /**
     * Use Given AbraFlexi for connections
     * 
     * @param int $abraflexiID
     * @param string $company Description
     */
    public function obtainLogo($abraflexiID,$company) {
        $abraflexir = new AbraFlexis($abraflexiID);
        $fbOptions = $abraflexir->getData();
        $fbOptions['company'] = $company;
        $logoEngine = new \AbraFlexi\ui\CompanyLogo(null, $fbOptions);
        return $logoEngine->getTagProperty('src');
    }

    
    
    /**
     * Convert data from AbraFlexi column names to SQL column names
     * 
     * @param arry $listing
     * 
     * @return array
     */
    public static function convertListingData($listing) {
        return [
            'company' => $listing['dbNazev'],
            'enabled' => $listing['show'],
            'name' => $listing['nazev'],
            'DatCreate' => $listing['createDt']
        ];
    }

    /**
     * Get Current record name
     * 
     * @return string
     */
    public function getRecordName() {
        return $this->getDataValue('nazev');
    }

    /**
     * 
     */
    public function prepareRemoteCompany() {
        $company = $this->companyPresentInAbraFlexi($data);
        if (empty($company)) {
            $companyInfo = $this->createCompanyInAbraFlexi($data);
            if (!empty($companyInfo)) {
                $this->setDataValue('company', $companyInfo ['dbNazev']);
            }
        } else {
            $this->setDataValue('company', $company);
        }

        $change = $this->prepareCompany($this->getDataValue('company'));
        $change['id'] = $this->getMyKey();
        $this->setData($change);
        $this->updateToSQL();
    }

    /**
     * Check for given company presence in AbraFlexi
     * 
     * @param array $companyData
     * 
     * @return string company dbNazev code
     */
    public function companyPresentInAbraFlexi($companyData = null) {
        if (is_null($companyData)) {
            $companyData = $this->getData();
        }
        $companyPresentInAbraFlexi = null;

        if (array_key_exists('company', $companyData)) {
            $this->getFlexiData('/c/' . $companyData['company']);
            if ($this->lastResponseCode == 200) {
                $companyPresentInAbraFlexi = $companyData['company'];
            }
        } elseif (array_key_exists('ic', $companyData)) {
            $candidates = $this->getFlexiData('/c');
            if (count($candidates)) {
                foreach ($candidates as $candidat) {
                    $nastaveni = $this->getFlexiData('/c/' . $candidat['dbNazev'] . '/nastaveni');
                    foreach ($nastaveni['nastaveni'] as $nast) {
                        if (array_key_exists('ic', $nast) || empty($nast['ic'])) {
                            if ($nast['ic'] == $companyData['ic']) {
                                $companyPresentInAbraFlexi = $candidat['dbNazev'];
                                break;
                            }
                        } else {
                            $this->addStatusMessage(sprintf(_('Company with no ID'),
                                            $candidat['nazev']), 'warning');
                        }
                        if (array_key_exists('nazFirmy', $nast) || empty($nast['nazFirmy'])) {
                            if ($nast['nazFirmy'] == $companyData['nazev']) {
                                $companyPresentInAbraFlexi = $candidat['dbNazev'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $companyPresentInAbraFlexi;
    }

    /**
     * Create new AbraFlexi company
     * 
     * @param string $companyData
     * 
     * @return array  new company info
     */
    public function createCompanyInAbraFlexi($companyData = null) {
        $companyInfo = null;
        if (is_null($companyData)) {
            $companyData = $this->getData();
        }

        if ($this->createNew($companyData['nazev'])) {
            $companies = $this->getColumnsFromAbraFlexi(['dbNazev',
                'createDt'], [], 'createDt');
            ksort($companies);

            $companyInfo = end($companies);

            $this->setCompany($companyInfo['dbNazev']);

            $this->addStatusMessage(sprintf(_('Company created'),
                            $this->getApiURL()), 'success');

            if (!empty($companyData['ic'])) {
                $setter = new \AbraFlexi\Nastaveni(null,
                        $this->getConnectionOptions());
                $settings = $setter->getAllFromAbraFlexi();
                foreach ($settings as $setting) {
                    $this->insertToAbraFlexi(['id' => $setting['id'], 'ic' => $companyData['ic']]);
                }
            }
        }
        return $companyInfo;
    }

    public function setEnvironment() {
        $envNames = [
            'ABRAFLEXI_URL' => $this->getDataValue('url'),
            'ABRAFLEXI_LOGIN' => $this->getDataValue('user'),
            'ABRAFLEXI_PASSWORD' => $this->getDataValue('password'),
            'ABRAFLEXI_COMPANY' => $this->getDataValue('company'),
            'EASE_MAILTO' => $this->getDataValue('email'),
            'EASE_LOGGER' => empty($this->getDataValue('email')) ? 'syslog' : 'syslog|email'
        ];
        $this->exportEnv($envNames);
    }

}
