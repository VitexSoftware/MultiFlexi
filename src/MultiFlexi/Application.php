<?php

/**
 * Multi Flexi  - App class
 *
 * @author     Vítězslav Dvořák <vitex@arachne.cz>
 * @copyright  2020-2023 Vitex Software
 */

namespace MultiFlexi;

/**
 * Description of Application
 *
 * @author vitex
 */
class Application extends Engine
{
    public $lastModifiedColumn;
    public $keyword;

    /**
     *
     * @var Company
     */
    public $company;

    /**
     *
     *
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = array())
    {
        $this->myTable = 'apps';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatUpdate';
        $this->keyword = 'app';
        $this->nameColumn = 'name';
        parent::__construct($identifier, $options);
        $this->company = new Company();
    }

    /**
     *
     * @return Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Check data before accepting
     *
     * @param array $data
     *
     * @return int
     */
    public function takeData($data)
    {
        $data['enabled'] = (($data['enabled'] == 'on') || ($data['enabled'] == 1));
        if (array_key_exists('name', $data) && empty($data['name'])) {
            $this->addStatusMessage(_('Name is empty'), 'warning');
        }

        if (array_key_exists('executable', $data) && ($this->checkExcutable($data['executable']) === false)) {
            $this->addStatusMessage(sprintf(_('Make sure the executable %s exists'), $data['executable']), 'info');
            $data['enabled'] = false; // Do not enable Application without existing command
        }

        if (array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir() . '/' . basename($_FILES['imageraw']['name']);
            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['image'] = 'data:' . mime_content_type($uploadfile) . ';base64,' . base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        }

        return parent::takeData($data);
    }

    /**
     * Check command's availbility
     *
     * @param string $command
     *
     * @return boolean check result
     */
    public function checkExcutable($command)
    {
        $status = true;
        if ($command[0] == '/') {
            if (file_exists($command) === false) {
                $this->addStatusMessage(sprintf(_('Executable %s does not exist'), $command), 'warning');
                $status = false;
            }
        } else {
            $executable = self::findBinaryInPath($command);
            if (empty($executable)) {
                $this->addStatusMessage(sprintf(_('Executable %s does not exist in search PATH %s'), $command, getenv('PATH')), 'warning');
                $status = false;
            } else {
                if (is_executable($executable) === false) {
                    $this->addStatusMessage(sprintf(_('file %s is not executable'), $command), 'warning');
                    $status = false;
                }
            }
        }
        return $status;
    }

    /**
     * Find real path for given binary name
     *
     * @param string $binary full realpath
     *
     * @return string
     */
    public static function findBinaryInPath($binary)
    {
        $found = null;
        if ($binary[0] == '/') {
            $found = file_exists($binary) && is_executable($binary) ? $binary : null;
        } else {
            foreach (strstr(getenv('PATH'), ':') ? explode(':', getenv('PATH')) : [getenv('PATH')] as $pathDir) {
                $candidat = ((substr($pathDir, -1) == '/') ? $pathDir : $pathDir . '/') . $binary;
                if (file_exists($candidat) && is_executable($candidat)) {
                    $found = $candidat;
                }
            }
        }
        return $found;
    }

    /**
     *
     * @param string $binary
     *
     * @return boolean
     */
    public static function doesBinaryExist($binary)
    {
        return ($binary[0] == '/') ? file_exists($binary) : self::isBinaryInPath($binary);
    }

    /**
     *
     * @param string $binary
     *
     * @return boolean
     */
    public static function isBinaryInPath($binary)
    {
        return !empty(self::findBinaryInPath($binary));
    }

    /**
     * For "platform" return applications by config fields
     *
     * @param string $platform AbraFlexi|Pohoda
     *
     * @return array
     */
    public function getPlatformApps($platform)
    {
        $platformApps = [];
        $confField = new Conffield();
        foreach ($this->listingQuery() as $appId => $appInfo) {
            $appConfFields = $confField->appConfigs($appInfo['id']);
            $appConfs = array_keys($appConfFields);
            if (preg_grep('/^' . strtoupper($platform) . '_.*/', $appConfs)) {
                $platformApps[$appId] = $appInfo;
            }
        }
        return $platformApps;
    }

    public function getAvailbleApps($platform)
    {
        return $this->listingQuery()->where('enabled', true);
    }

    /**
     * Export Application and its Fields definiton as Json
     *
     * @return string Json
     */
    public function getAppJson()
    {
        $appData = $this->getData();
        if ($this->getMyKey()) {
            $confField = new Conffield();
            $appData['environment'] = $confField->appConfigs($appData['id']);
        } else {
            $appData['environment'] = [];
        }

        foreach ($appData['environment'] as $fieldName => $filedProperties) {
            unset($appData['environment'][$fieldName]['id']);
            unset($appData['environment'][$fieldName]['keyname']);
            unset($appData['environment'][$fieldName]['app_id']);
        }

        unset($appData['id']);
        unset($appData['DatCreate']);
        unset($appData['DatUpdate']);
        unset($appData['enabled']);
        $appData['multiflexi'] = \Ease\Shared::appName() . ' v' . \Ease\Shared::appVersion() . ' @' . gethostbyaddr('127.0.1.1') . ' ' . gmdate('Y-m-d h:i:s \G\M\T');
        return json_encode($appData, JSON_PRETTY_PRINT);
    }

    /**
     * valid filename for current App Json
     *
     * @return string
     */
    public function jsonFileName()
    {
        return strtolower(trim(preg_replace('#\W+#', '_', strval($this->getRecordName())), '_')) . '.multiflexi.app.json';
    }

    /**
     * import json exported
     *
     * @param string $jsonFile
     *
     * @return array
     */
    public function importAppJson($jsonFile)
    {
        $fields = [];
        $importData = json_decode(file_get_contents($jsonFile), true);
        if (is_array($importData)) {
            $importData['enabled'] = 'on';

            $environment = array_key_exists('environment', $importData) ? $importData['environment'] : [];
            unset($importData['environment']);
            $this->addStatusMessage('Importing ' . $importData['name'] . ' from ' . $jsonFile . ' created by ' . $importData['multiflexi'], 'debug');
            unset($importData['multiflexi']);
            $this->takeData($importData);

            $candidat = $this->listingQuery()->where('executable', $importData['executable'])->whereOr('name', $importData['name']);
            if ($candidat->count()) {
                $this->setMyKey($candidat->fetchColumn());
            }

            if ($this->dbsync()) {
                $fields = [$importData['name']];
                if (empty($environment) === false) {
                    $confField = new Conffield();
                    foreach ($environment as $envName => $envProperties) {
                        if ($confField->addAppConfig($this->getMyKey(), $envName, $envProperties)) {
                            $fields[] = $envName;
                        }
                    }
                }
                $this->addStatusMessage('Import:' . implode(',', $fields), 'success');

                $executable = Application::findBinaryInPath($this->getDataValue('executable'));
                if (empty($executable)) {
                    $this->addStatusMessage(sprintf(_('executable %s not found'), $this->getDataValue('executable')), 'warning');
                    if (array_key_exists('deploy', $importData) && !empty($envProperties['deploy'])) {
                        $this->addStatusMessage(sprintf(_('consider: %s'), $importData['deploy']), 'info');
                    }
                }
            }
        }
        return $fields;
    }
}
