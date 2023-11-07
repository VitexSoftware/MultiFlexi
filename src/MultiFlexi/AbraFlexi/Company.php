<?php

namespace MultiFlexi\AbraFlexi;

/**
 * Multi Flexi - AbraFlexiCompany Management Class
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2018-2023 Vitex Software
 */
class Company extends \AbraFlexi\Company implements \MultiFlexi\platformCompany
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
        $this->setupProperty($options, 'company', 'ABRAFLEXI_COMPANY');
        $this->setupProperty($options, 'url', 'ABRAFLEXI_URL');
        $this->setupProperty($options, 'user', 'ABRAFLEXI_LOGIN');
        $this->setupProperty($options, 'password', 'ABRAFLEXI_PASSWORD');
        $this->setupProperty($options, 'authSessionId', 'ABRAFLEXI_AUTHSESSID');
        $this->setupProperty($options, 'timeout', 'ABRAFLEXI_TIMEOUT');
        $this->setupProperty($options, 'nativeTypes', 'ABRAFLEXI_NATIVE_TYPES');
        if (is_integer($init)) {
            $this->loadFromSQL($init);
            $this->setCompany($this->getDataValue('company'));
        }
        $this->updateApiURL();
        $this->curlInit();
    }

    /**
     * Prepare company
     *
     * @param string $company
     *
     * @return array
     */
    public function prepareCompany($company)
    {
        $result = ['webhook' => false];
        $this->setCompany($company);
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
    public static function webHookUrl($instanceId)
    {
        $baseUrl = \Ease\Document::phpSelf();
        $urlInfo = parse_url($baseUrl);
        $curFile = basename($urlInfo['path']);
        $webHookUrl = str_replace(
            $curFile,
            'webhook.php?instanceid=' . $instanceId,
            $baseUrl
        );
        return $webHookUrl;
    }

    /**
     * Add requied AbraFlexi label to company
     *
     * @param string $label
     *
     * @return boolean
     */
    public function addAbraFlexiLabel($label)
    {
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
                $stitek->addStatusMessage(
                    sprintf(_('label %s created'), $label),
                    'success'
                );
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
    public function registerWebHook($hookurl)
    {
        $format = 'json';
        $hooker = new \AbraFlexi\Hooks(null, $this->getData());
        $hooker->setDataValue('skipUrlTest', 'true');
        $hookResult = $hooker->register($hookurl, $format);
        if ($hookResult) {
            $hooker->addStatusMessage(sprintf(
                _('Hook %s was registered'),
                $hookurl
            ), 'success');
            $hookurl = '';
        } else {
            $hooker->addStatusMessage(sprintf(
                _('Hook %s not registered'),
                $hookurl
            ), 'warning');
        }
        return $hookResult;
    }

    /**
     * Eanble Or disble ChangesAPI
     *
     * @param boolean $enable requested state
     *
     * @return boolean
     */
    public function changesApi($enable)
    {
        $changer = new \AbraFlexi\Changes(null, $this->getData());
        $chapistatus = $changer->getStatus();
        //        $globalVersion = $changer->getGlobalVersion();

        if ($enable === true) {
            if ($chapistatus === false) {
                $changer->enable();
                $changer->addStatusMessage(
                    _('ChangesAPI was enabled'),
                    'success'
                );
                $chapistatus = true;
            }
        } else {
            if ($chapistatus === true) {
                $changer->disable();
                $changer->addStatusMessage(
                    _('ChangesAPI was disabled'),
                    'warning'
                );
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
        // TODO: $data['logo'] = $this->obtainLogo(intval($data['server']), $data['company']);
        return parent::takeData($data);
    }

    /**
     * Načte záznam z AbraFlexi a uloží v sobě jeho data
     * Read AbraFlexi record and store it inside od object
     *
     * @param int|string|array $id ID or conditions
     *
     * @return int počet načtených položek
     */
    public function loadFromAbraFlexi($id = null)
    {
        $data = [];
        if (is_null($id)) {
            $id = $this->getMyKey();
        }

        $flexidata = $this->getFlexiData($this->getEvidenceUrl() . '/' . (is_array($id) ? '' : self::urlizeId($id)), (is_array($id) ? $id : ''));
        if ($this->lastResponseCode == 200) {
            $this->apiURL = $this->curlInfo['url'];
            if (is_array($flexidata) && (count($flexidata) == 1) && is_array(current($flexidata))) {
                $data = current($flexidata);
            }
            $data['server'] = $this->abraflexiId;
            $data['company'] = $this->getCompany();
            unset($data['id']);
        }
        return $this->takeData($data);
    }

    /**
     * Use Given AbraFlexi for connections
     *
     * @param int $abraflexiID
     * @param string $company Description
     */
    public function obtainLogo($abraflexiID, $company)
    {
        $abraflexir = new Server($abraflexiID);
        $fbOptions = $abraflexir->getData();
        $fbOptions['company'] = $company;
        $logoEngine = new \AbraFlexi\ui\CompanyLogo(null, $fbOptions);
        return $logoEngine->getTagProperty('src');
    }

    /**
     * Convert data from AbraFlexi column names to SQL column names
     *
     * @param array $listing
     *
     * @return array
     */
    public static function convertListingData($listing)
    {
        return [
            'company' => $listing['dbNazev'],
            'enabled' => $listing['show'],
            'name' => $listing['name'],
            'DatCreate' => $listing['createDt']
        ];
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
     *
     */
    public function prepareRemoteCompany($data)
    {
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
    public function companyPresentInAbraFlexi($companyData = null)
    {
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
                            $this->addStatusMessage(sprintf(
                                _('Company with no ID'),
                                $candidat['name']
                            ), 'warning');
                        }
                        if (array_key_exists('nazFirmy', $nast) || empty($nast['nazFirmy'])) {
                            if ($nast['nazFirmy'] == $companyData['name']) {
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
     * Create new \AbraFlexi company
     *
     * @param string $companyData
     *
     * @return array  new company info
     */
    public function createCompanyInAbraFlexi($companyData = null)
    {
        $companyInfo = null;
        if (is_null($companyData)) {
            $companyData = $this->getData();
        }

        if ($this->createNew($companyData['name'])) {
            $companies = $this->getColumnsFromAbraFlexi(['dbNazev',
                'createDt'], [], 'createDt');
            ksort($companies);
            $companyInfo = end($companies);
            $this->setCompany($companyInfo['dbNazev']);
            $this->addStatusMessage(sprintf(
                _('Company created'),
                $this->getApiURL()
            ), 'success');
            if (!empty($companyData['ic'])) {
                $setter = new \AbraFlexi\Nastaveni(
                    null,
                    $this->getConnectionOptions()
                );
                $settings = $setter->getAllFromAbraFlexi();
                foreach ($settings as $setting) {
                    $this->insertToAbraFlexi(['id' => $setting['id'], 'ic' => $companyData['ic']]);
                }
            }
        }
        return $companyInfo;
    }

    /**
     *
     */
    public function setEnvironment()
    {
        $envNames = [
            'ABRAFLEXI_URL' => $this->getDataValue('url'),
            'ABRAFLEXI_LOGIN' => $this->getDataValue('user'),
            'ABRAFLEXI_PASSWORD' => $this->getDataValue('password'),
            'ABRAFLEXI_COMPANY' => $this->getDataValue('company'),
            'EASE_EMAILTO' => $this->getDataValue('email'),
            'EASE_LOGGER' => empty($this->getDataValue('email')) ? 'syslog' : 'syslog|email'
        ];
        // $this->exportEnv($envNames);
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

    public function getServerEnvironment()
    {
        $server = new Server($this->getDataValue('server'));
        return $server->getEnvironment();
    }

    public function getEnvironment()
    {
        $serverEnvironment = $this->getServerEnvironment();
        $companyEnvHelper = new \MultiFlexi\CompanyEnv($this->getMyKey());
        $companyEnvironment = $companyEnvHelper->getData();
        $companyEnvironment['ABRAFLEXI_COMPANY'] = $this->getCompany();
        return array_merge($serverEnvironment, $companyEnvironment);
    }

    public function getLogo()
    {
        $configurator = new \AbraFlexi\Nastaveni(null, $options);
        try {
            $logoInfo = $configurator->getFlexiData('1/logo');
        } catch (\AbraFlexi\Exception $ex) {
            $logoInfo = false;
        }
    }
}
