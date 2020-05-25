<?php

namespace FlexiPeeHP\MultiSetup;

/**
 * Multi FlexiBee Setup - Company Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2020 Vitex Software
 */
class Company extends \FlexiPeeHP\Company {

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
//        $result['labels'] = $this->addFlexiBeeLabel('TaxTorro') && $this->addFlexiBeeLabel('DataMolino');
        if ($this->changesApi(true)) {
            $result['webhook'] = $this->registerWebHook(self::webHookUrl($this->getMyKey()));
        }
        return $result;
    }

    /**
     * WebHook url for Given ID of FlexiBee instance
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
     * Add requied FlexiBee label to company
     * 
     * @param string $label
     * 
     * @return boolean
     */
    public function addFlexiBeeLabel($label) {
        $result = true;
        $evidenceToVsb = array_flip(\FlexiPeeHP\Stitek::$vsbToEvidencePath);
        /**
         * @var \FlexiPeeHP\Stitek Label Object
         */
        $stitek = new \FlexiPeeHP\Stitek(null, $this->getConnectionOptions());
        /**
         * @see https://demo.flexibee.eu/c/demo/stitek/properties
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

        $stitekID = $stitek->getColumnsFromFlexibee('id', $stitekData);

        if (!isset($stitekID[0]['id'])) {
            $stitek->insertToFlexiBee($stitekData);
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
        $hooker = new \FlexiPeeHP\Hooks(null, $this->getData());
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
        $changer = new \FlexiPeeHP\Changes(null, $this->getData());
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

        $data['logo'] = $this->obtainLogo(intval($data['flexibee']),$data['company']);

        return parent::takeData($data);
    }

    /**
     * Use Given FlexiBee for connections
     * 
     * @param int $flexiBeeID
     * @param string $company Description
     */
    public function obtainLogo($flexiBeeID,$company) {
        $flexibeer = new FlexiBees($flexiBeeID);
        $fbOptions = $flexibeer->getData();
        $fbOptions['company'] = $company;
        $logoEngine = new \FlexiPeeHP\ui\CompanyLogo(null, $fbOptions);
        return $logoEngine->getTagProperty('src');
    }

    
    
    /**
     * Convert data from FlexiBee column names to SQL column names
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
        $company = $this->companyPresentInFlexiBee($data);
        if (empty($company)) {
            $companyInfo = $this->createCompanyInFlexiBee($data);
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
     * Check for given company presence in FlexiBee
     * 
     * @param array $companyData
     * 
     * @return string company dbNazev code
     */
    public function companyPresentInFlexiBee($companyData = null) {
        if (is_null($companyData)) {
            $companyData = $this->getData();
        }
        $companyPresentInFlexiBee = null;

        if (array_key_exists('company', $companyData)) {
            $this->getFlexiData('/c/' . $companyData['company']);
            if ($this->lastResponseCode == 200) {
                $companyPresentInFlexiBee = $companyData['company'];
            }
        } elseif (array_key_exists('ic', $companyData)) {
            $candidates = $this->getFlexiData('/c');
            if (count($candidates)) {
                foreach ($candidates as $candidat) {
                    $nastaveni = $this->getFlexiData('/c/' . $candidat['dbNazev'] . '/nastaveni');
                    foreach ($nastaveni['nastaveni'] as $nast) {
                        if (array_key_exists('ic', $nast) || empty($nast['ic'])) {
                            if ($nast['ic'] == $companyData['ic']) {
                                $companyPresentInFlexiBee = $candidat['dbNazev'];
                                break;
                            }
                        } else {
                            $this->addStatusMessage(sprintf(_('Company with no ID'),
                                            $candidat['nazev']), 'warning');
                        }
                        if (array_key_exists('nazFirmy', $nast) || empty($nast['nazFirmy'])) {
                            if ($nast['nazFirmy'] == $companyData['nazev']) {
                                $companyPresentInFlexiBee = $candidat['dbNazev'];
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $companyPresentInFlexiBee;
    }

    /**
     * Create new FlexiBee company
     * 
     * @param string $companyData
     * 
     * @return array  new company info
     */
    public function createCompanyInFlexiBee($companyData = null) {
        $companyInfo = null;
        if (is_null($companyData)) {
            $companyData = $this->getData();
        }

        if ($this->createNew($companyData['nazev'])) {
            $companies = $this->getColumnsFromFlexibee(['dbNazev',
                'createDt'], [], 'createDt');
            ksort($companies);

            $companyInfo = end($companies);

            $this->setCompany($companyInfo['dbNazev']);

            $this->addStatusMessage(sprintf(_('Company created'),
                            $this->getApiURL()), 'success');

            if (!empty($companyData['ic'])) {
                $setter = new \FlexiPeeHP\Nastaveni(null,
                        $this->getConnectionOptions());
                $settings = $setter->getAllFromFlexibee();
                foreach ($settings as $setting) {
                    $this->insertToFlexiBee(['id' => $setting['id'], 'ic' => $companyData['ic']]);
                }
            }
        }
        return $companyInfo;
    }

}
