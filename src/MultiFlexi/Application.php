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
 * Description of Application.
 *
 * @author vitex
 */
class Application extends DBEngine
{
    public ?string $lastModifiedColumn;
    public Company $company;
    public static string $appSchema = __DIR__.'/../../lib/multiflexi.app.schema.json';

    /**
     * @param mixed $identifier
     * @param array $options
     */
    public function __construct($identifier = null, $options = [])
    {
        $this->keyword = 'app';
        $this->myTable = 'apps';
        $this->createColumn = 'DatCreate';
        $this->lastModifiedColumn = 'DatUpdate';
        $this->keyword = 'app';
        $this->nameColumn = 'name';
        parent::__construct($identifier, $options);
        $this->company = new Company();
    }

    /**
     * @return \MultiFlexi\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Check data before accepting.
     */
    #[\Override]
    public function takeData(array $data): int
    {
        $data['enabled'] = \array_key_exists('enabled', $data) ? (($data['enabled'] === 'on') || ($data['enabled'] === 1)) : 0;

        if (\array_key_exists('name', $data) && empty($data['name'])) {
            $this->addStatusMessage(_('Name is empty'), 'warning');
        }

        if (\array_key_exists('imageraw', $_FILES) && !empty($_FILES['imageraw']['name'])) {
            $uploadfile = sys_get_temp_dir().'/'.basename($_FILES['imageraw']['name']);

            if (move_uploaded_file($_FILES['imageraw']['tmp_name'], $uploadfile)) {
                $data['image'] = 'data:'.mime_content_type($uploadfile).';base64,'.base64_encode(file_get_contents($uploadfile));
                unlink($uploadfile);
                unset($data['imageraw']);
            }
        }

        //        if ((\array_key_exists('uuid', $data) === false) || empty($data['uuid'])) {
        //            $data['uuid'] = \Ease\Functions::guidv4();
        //        }

        if ((\array_key_exists('code', $data) === false) || empty($importData['code'])) {
            //            $data['code'] = substr(substr(strtoupper($data['executable'] ? basename($data['executable']) : $data['name']), -7), 0, 6);
        }

        unset($data['class']);

        return parent::takeData($data);
    }

    public function getCode()
    {
        $data = $this->getData();

        return substr(strtoupper($data['executable'] ? basename($data['executable']) : $data['name']), 0, -6);
    }

    public function getUuid()
    {
        return \Ease\Functions::guidv4();
    }

    /**
     * Check command's availbility.
     *
     * @param string $command
     *
     * @return bool check result
     */
    public function checkExcutable($command)
    {
        //        new \Symfony\Component\Process\ExecutableFinder(); TODO

        $status = true;

        if ($command[0] === '/') {
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
     * Find real path for given binary name.
     *
     * @param string $binary full realpath
     *
     * @return string
     */
    public static function findBinaryInPath($binary)
    {
        $found = null;

        if ($binary[0] === '/') {
            $found = file_exists($binary) && is_executable($binary) ? $binary : null;
        } else {
            foreach (strstr(getenv('PATH'), ':') ? explode(':', getenv('PATH')) : [getenv('PATH')] as $pathDir) {
                $candidat = ((substr($pathDir, -1) === '/') ? $pathDir : $pathDir.'/').$binary;

                if (file_exists($candidat) && is_executable($candidat)) {
                    $found = $candidat;

                    break;
                }
            }
        }

        return $found;
    }

    /**
     * @param string $binary
     *
     * @return bool
     */
    public static function doesBinaryExist($binary)
    {
        return ($binary[0] === '/') ? file_exists($binary) : self::isBinaryInPath($binary);
    }

    /**
     * @param string $binary
     *
     * @return bool
     */
    public static function isBinaryInPath($binary)
    {
        return !empty(self::findBinaryInPath($binary));
    }

    /**
     * For "platform" return applications by config fields.
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

            if (preg_grep('/^'.strtoupper($platform).'_.*/', $appConfs)) {
                $platformApps[$appId] = $appInfo;
            }
        }

        return $platformApps;
    }

    /**
     * Obtain list of applications supporting given platform.
     *
     * @param string $platform
     *
     * @return \Envms\FluentPDO\Query
     */
    public function getAvailbleApps($platform)
    {
        return $this->listingQuery()->where('enabled', true);
    }

    /**
     * Export Application and its Fields definiton as Json.
     *
     * @return string Json
     */
    public function getAppJson()
    {
        $appData = $this->getData();

        if ($this->getMyKey()) {
            $confField = new Conffield();
            $appData['environment'] = $confField->getAppConfigs($this)->getEnvArray();
        } else {
            $appData['environment'] = [];
        }

        foreach ($appData['environment'] as $fieldName => $filedProperties) {
            unset($appData['environment'][$fieldName]['id'], $appData['environment'][$fieldName]['keyname'], $appData['environment'][$fieldName]['app_id']);
        }

        unset($appData['id'], $appData['DatCreate'], $appData['DatUpdate'], $appData['enabled']);

        $appData['multiflexi'] = \Ease\Shared::appVersion();

        return json_encode($appData, \JSON_PRETTY_PRINT);
    }

    /**
     * valid filename for current App Json.
     *
     * @return string
     */
    public function jsonFileName()
    {
        return strtolower(trim(preg_replace('#\W+#', '_', (string) $this->getRecordName()), '_')).'.multiflexi.app.json';
    }

    /**
     * import Json App Definition file.
     *
     * @param string $jsonFile
     *
     * @return array
     */
    public function importAppJson($jsonFile)
    {
        $fields = [];

        // Validate JSON against schema before import using justinrainbow/json-schema
        $schemaFile = self::$appSchema;

        if (file_exists($schemaFile)) {
            $data = json_decode(file_get_contents($jsonFile));
            $validator = new \JsonSchema\Validator();
            $validator->validate($data, (object) ['$ref' => 'file://'.realpath($schemaFile)]);

            if (!$validator->isValid()) {
                $errorMsg = "JSON does not validate. Violations:\n";

                foreach ($validator->getErrors() as $error) {
                    $errorMsg .= sprintf("[%s] %s\n", $error['property'], $error['message']);
                }

                $this->addStatusMessage($errorMsg, 'error');

                return [];
            }
        } else {
            $this->addStatusMessage('JSON schema file not found: '.$schemaFile, 'warning');
        }

        $codes = $this->listingQuery()->select('code', true)->fetchAll('code');

        $appSpecRaw = file_get_contents($jsonFile);

        if (empty($appSpecRaw) === false) {
            $importData = json_decode($appSpecRaw, true);

            if (\is_array($importData)) {
                $importData['enabled'] = 'on';
                $importData['image'] = '';
                $environment = \array_key_exists('environment', $importData) ? $importData['environment'] : [];
                unset($importData['environment']);
                $this->addStatusMessage('Importing '.$importData['name'].' from '.$jsonFile.' created by '.$importData['multiflexi'], 'debug');
                unset($importData['multiflexi']);
                $importData['requirements'] = \array_key_exists('requirements', $importData) ? (string) ($importData['requirements']) : '';

                if (\array_key_exists('uuid', $importData) && !empty($importData['uuid'])) {
                    $candidat = $this->listingQuery()->where('uuid', $importData['uuid']);
                    $this->setDataValue('uuid', $importData['uuid']);
                } else {
                    $this->addStatusMessage(_('UUID field not present. '), 'error');

                    exit(1);
                }

                $newVersion = \array_key_exists('version', $importData) ? $importData['version'] : 'n/a';
                $newName = $importData['name'];

                if ($candidat->count()) { // Update
                    $this->setMyKey($candidat->fetchColumn());
                    $currentData = $candidat->fetchAll();
                    $currentVersion = \array_key_exists('version', $currentData[0]) ? $currentData[0]['version'] : 'n/a';
                    $currentName = $currentData[0]['name'];
                    // $this->addStatusMessage(sprintf(_('Current Record: #%s - %s'), $currentData[0]['id'], $currentData[0]['name']), 'debug');
                } else { // Insert
                    $currentVersion = 'n/a';
                    $currentName = '';
                    $currentData[0] = [];

                    if ((\array_key_exists('code', $importData) === false) || empty($importData['code'])) {
                        $importData['code'] = substr(substr(strtoupper($importData['executable'] ? basename($importData['executable']) : $importData['name']), -7), 0, 6);
                        $pos = 0;

                        while (\array_key_exists($importData['code'], $codes)) {
                            $importData['code'] = substr(substr(strtoupper($importData['executable'] ? basename($importData['executable']) : $importData['name']), -7), 0, 5).$pos++;
                        }
                    }
                }

                if (\array_key_exists('topics', $importData) === false) {
                    $this->addStatusMessage(_('Topics field not present. '), 'warning');
                }

                if (($currentVersion !== 'n/a') && $currentVersion === $newVersion) {
                    $this->addStatusMessage('🧩📦 '.$importData['name'].' ('.$currentVersion.') already present', 'info');
                    $fields = [true];
                } else {
                    if ($currentName === $newName) {
                        unset($importData['name']);
                    }

                    $this->takeData($importData);

                    try {
                        // $this->addStatusMessage(sprintf(_('Saving #%s - %s'), $this->getMyKey(), $this->getRecordName() . ' ' . $this->getDataValue('uuid')), 'debug');

                        if ($this->dbsync()) {
                            unset($currentData[0]['id'], $currentData[0]['name'], $currentData[0]['DatCreate'], $currentData[0]['DatUpdate'], $currentData[0]['code']);
                            $fields = array_diff(array_keys($currentData[0]), array_keys($importData));

                            if (empty($environment) === false) {
                                $confField = new Conffield();

                                foreach ($environment as $envName => $envProperties) {
                                    if ($confField->addAppConfig($this->getMyKey(), $envName, $envProperties)) {
                                        $fields[] = $envName;
                                    }
                                }
                            }

                            $this->addStatusMessage('🧩📦 '.$this->getRecordName().'('.$currentVersion.' ➟ '.$newVersion.'): '.implode(',', $fields), 'success');
                            $executable = self::findBinaryInPath($this->getDataValue('executable'));

                            if (empty($executable)) {
                                $this->addStatusMessage(sprintf(_('executable %s not found'), $this->getDataValue('executable')), 'warning');

                                if (\array_key_exists('deploy', $importData) && !empty($envProperties['deploy'])) {
                                    $this->addStatusMessage(sprintf(_('consider: %s'), $importData['deploy']), 'info');
                                }
                            }

                            if (empty($fields)) {
                                $fields = [true];
                            }

                            $topics = $this->getDataValue('topics');

                            if ($topics) {
                                $toptopic = new TopicManger();
                                $toptopic->add(strstr($topics, ',') ? explode(',', $topics) : [$topics]);
                            }
                        }
                    } catch (\PDOException $exc) {
                        echo $exc->getTraceAsString();
                        $problemData = $this->getData();

                        if (\array_key_exists('image', $currentData)) {
                            $problemData['image'] = substr((string) $problemData['image'], 0, 20).' ...';
                        }

                        fwrite(\STDERR, print_r($problemData, true).\PHP_EOL);
                        echo $exc->getMessage();
                    }
                }
            }
        } else {
            $this->addStatusMessage(sprintf(_('The %s does not contain valid json').' '.json_last_error_msg(), $jsonFile), 'error');
        }

        return $fields;
    }

    /**
     * Remove Application by its json definition.
     *
     * @param string $jsonFile path to definition
     *
     * @return bool app removal status
     */
    public function jsonAppRemove($jsonFile)
    {
        $success = true;
        $importData = json_decode(file_get_contents($jsonFile), true);

        if (\is_array($importData)) {
            $candidat = $this->listingQuery()->where('uuid', $importData['uuid']);

            if ($candidat->count()) {
                foreach ($candidat as $candidatData) {
                    $this->setMyKey($candidatData['id']);
                    $removed = $this->deleteFromSQL();

                    if (null === $removed) {
                        $success = false;
                    }

                    $this->addStatusMessage(sprintf(_('Application removal %d %s'), $candidatData['id'], $candidatData['name']), \is_int($removed) ? 'success' : 'error');
                }
            }
        }

        return $success;
    }

    /**
     * Smaže záznam z SQL.
     *
     * @param array|int $data
     *
     * @return bool
     */
    public function deleteFromSQL($data = null)
    {
        if (null === $data) {
            $data = $this->getData();
        }

        $appId = $this->getMyKey($data);

        $a2c = $this->getFluentPDO()->deleteFrom('companyapp')->where('app_id', $appId)->execute();

        if ($a2c !== 0) {
            $this->addStatusMessage(sprintf(_('Unassigned from %d companies'), $a2c), null === $a2c ? 'error' : 'success');
        }

        $runtemplates = $this->getFluentPDO()->from('runtemplate')->where('app_id', $appId)->fetchAll();

        foreach ($runtemplates as $runtemplate) {
            $rt2ac = $this->getFluentPDO()->deleteFrom('actionconfig')->where('runtemplate_id', $runtemplate['id'])->execute();

            if ($rt2ac !== 0) {
                $this->addStatusMessage(sprintf(_('%s Action Config removal'), $runtemplate['name']), null === $rt2ac ? 'error' : 'success');
            }
        }

        $runtemplater = new RunTemplate();

        foreach ($runtemplater->listingQuery()->where('app_id', $appId) as $runtemplateData) {
            $this->addStatusMessage(sprintf(_('#%d %s RunTemplate removal'), $runtemplateData['id'], $runtemplateData['name']), $runtemplater->deleteFromSQL($runtemplateData['id']) ? 'error' : 'success');
        }

        $a2cf = $this->getFluentPDO()->deleteFrom('conffield')->where('app_id', $appId)->execute();

        if ($a2cf !== 0) {
            $this->addStatusMessage(sprintf(_('%d Config fields removed'), $a2cf), null === $a2cf ? 'error' : 'success');
        }

        $a2cfg = $this->getFluentPDO()->deleteFrom('configuration')->where('app_id', $appId)->execute();

        if ($a2cfg !== 0) {
            $this->addStatusMessage(sprintf(_('%d Configurations removed'), $a2cfg), null === $a2cfg ? 'error' : 'success');
        }

        $a2job = $this->getFluentPDO()->deleteFrom('job')->where('app_id', $appId)->execute();

        if ($a2job !== 0) {
            $this->addStatusMessage(sprintf(_('%d Jobs removed'), $a2job), null === $a2job ? 'error' : 'success');
        }

        return parent::deleteFromSQL($this->getMyKey($data));
    }

    /**
     * Configuration Fields with metadata.
     *
     * @return array
     */
    public function getAppEnvironmentFields()
    {
        return Conffield::getAppConfigs($this);
    }

    /**
     * Application Requirements as Array.
     *
     * @return array<string>
     */
    public function getRequirements(): array
    {
        $reqs = (string) $this->getDataValue('requirements');

        return \strlen($reqs) ? (strstr($reqs, ',') ? explode(',', $reqs) : [$reqs]) : [];
    }

    /**
     * @param array $columns
     *
     * @return array
     */
    public function columns($columns = [])
    {
        return parent::columns([
            ['name' => 'id', 'type' => 'text', 'label' => _('ID'),
                'detailPage' => 'app.php', 'valueColumn' => 'apps.id', 'idColumn' => 'apps.id', ],
            ['name' => 'icon', 'type' => 'text', 'label' => _('Icon'), 'searchable' => false],
            ['name' => 'name', 'type' => 'text', 'label' => _('Name')],
            ['name' => 'description', 'type' => 'text', 'label' => _('Description')],
            ['name' => 'version', 'type' => 'text', 'label' => _('Version')],
            ['name' => 'topics', 'type' => 'text', 'label' => _('Topics')],
            ['name' => 'executable', 'type' => 'text', 'label' => _('Executable')],
            ['name' => 'uuid', 'type' => 'text', 'label' => _('UUID')],
            ['name' => 'ociimage', 'type' => 'text', 'label' => _('OCI Image')],
        ]);
    }

    public function completeDataRow(array $dataRowRaw)
    {
        $dataRow = current(Ui\AppsSelector::translateColumns([$dataRowRaw], ['name', 'description']));
        $dataRow['name'] = '<a title="'.$dataRowRaw['name'].'" href="app.php?id='.$dataRowRaw['id'].'">'.$dataRowRaw['name'].'</a>';
        $dataRow['icon'] = '<a title="'.$dataRowRaw['name'].'" href="app.php?id='.$dataRowRaw['id'].'"><img src="appimage.php?uuid='.$dataRowRaw['uuid'].'" height="50">';

        $topics = new \Ease\Html\DivTag();

        if (empty($dataRow['topics']) === false) {
            foreach (explode(',', $dataRow['topics']) as $topic) {
                $topics->addItem(new \Ease\TWB4\Badge('secondary', $topic));
            }

            $dataRow['topics'] = (string) $topics;
        }

        //        $dataRowRaw['created'] = (new LiveAge((new DateTime($dataRowRaw['created']))))->__toString();

        return parent::completeDataRow($dataRow);
    }

    public function checkRequiredFields(array $keysValues, bool $verbose = false): bool
    {
        $ok = true;

        foreach ($this->getAppEnvironmentFields() as $fieldName => $field) {
            if ($field->isRequired() && empty($field->getValue())) {
                $this->addStatusMessage(sprintf(_('the required configuration key `%s` was not filled'), $fieldName), 'warning');
                $ok = false;
            }
        }

        return $ok;
    }

    public function loadImage($uuid, $prefix): bool
    {
        $imageFile = $prefix.$this->getDataValue('uuid').'.svg';

        if (file_exists($imageFile)) {
            $this->setDataValue('icon', 'data:image/svg+xml;base64,'.file_get_contents($filename));
            $result = true;
        } else {
            $result = false;
        }

        return $result;
    }
}
