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

use Ease\Anonym;
use Ease\Shared;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];

if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (\Ease\Shared::cfg('APP_DEBUG') === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexi cli');
Shared::user(new Anonym());

$command = \array_key_exists(1, $argv) ? $argv[1] : 'help';
$argument = \array_key_exists(2, $argv) ? $argv[2] : null;
$identifier = \array_key_exists(3, $argv) ? $argv[3] : null;
$property = \array_key_exists(4, $argv) ? $argv[4] : null;
$option = \array_key_exists(5, $argv) ? $argv[5] : null;

// Collect properties starting with --
$properties = [];

$probBegin = 0;

for ($i = 1; $i < \count($argv); ++$i) {
    if (strpos($argv[$i], '--') === 0) {
        $probBegin = $i;

        break;
    }
}

if ($probBegin) {
    for ($i = $probBegin; $i < \count($argv); ++$i) {
        if (strpos($argv[$i], '--') === 0) {
            $key = substr($argv[$i], 2);
            $value = \array_key_exists($i + 1, $argv) ? $argv[$i + 1] : null;
            $properties[$key] = $value;
            ++$i; // Skip the next argument as it is the value
        }
    }
}

switch ($command) {
    case 'version':
        echo \Ease\Shared::appName().' '.\Ease\Shared::appVersion()."\n";

        break;
    case 'remove':
        switch ($argument) {
            case 'user':
                $engine = new \MultiFlexi\User(is_numeric($identifier) ? (int) $identifier : $identifier);

                break;
            case 'app':
                $engine = new \MultiFlexi\Application((int) $identifier);

                break;
            case 'company':
                $engine = new \MultiFlexi\Company(is_numeric($identifier) ? (int) $identifier : ['code' => $identifier], ['autoload' => 'true']);

                break;
            case 'runtemplate':
                $engine = new \MultiFlexi\RunTemplate((int) $identifier);

                break;

            default:
                echo $argv[0].' remove <sql row id or other identifier>';

                break;
        }

        $name = $engine->getRecordName();
        $engine->addStatusMessage(sprintf(_('%s name removal'), $name), $engine->deleteFromSQL() ? 'success' : 'error');

        break;
    case 'add':
        switch ($argument) {
            case 'user':
                if (empty($identifier)) {
                    echo $argv[0].' add user <login> <email>';

                    exit;
                }

                $checkData = ['login' => (string) $identifier, 'email' => $property];
                $engine = new \MultiFlexi\User($checkData, ['autoload' => false]);

                break;
            case 'app':
                if (empty($identifier)) {
                    echo $argv[0].' add app <executable> <name>';

                    exit;
                }

                $checkData = ['executable' => (string) $identifier, 'name' => $property];
                $engine = new \MultiFlexi\Application($checkData, ['autoload' => false]);

                break;
            case 'company':
                if (empty($identifier)) {
                    echo $argv[0].' add company <code> <name>';

                    exit;
                }

                $checkData = ['code' => (string) $identifier];
                $engine = new \MultiFlexi\Company(['code' => (string) $identifier, 'name' => $property ? $property : $identifier], ['autoload' => false]);

                break;
            case 'runtemplate':
                if (empty($identifier)) {
                    echo $argv[0].' add runtemplate <name> <app name/id> <company code/id> [--CONFIG_FIELD=VALUE ..]';

                    exit;
                }

                $apper = new Application();
                $companer = new Company();

                if (is_numeric($option)) {
                    $company_id = $option;
                } else {
                    $company_id = $companer->listingQuery()->select('id', true)->where('code', $option)->fetchColumn();

                    if (\is_bool($company_id)) {
                        fwrite(\STDERR, sprintf(_('Company "%s" not found.').\PHP_EOL, $option));

                        exit(1);
                    }
                }

                if (is_numeric($property)) {
                    $app_id = $property;
                } else {
                    $app_id = $apper->listingQuery()->select('id', true)->where('name', $property)->fetchColumn();

                    if (\is_bool($app_id)) {
                        fwrite(\STDERR, sprintf(_('Application "%s" not found.').\PHP_EOL, $property));

                        exit(1);
                    }
                }

                $apper->loadFromSQL($app_id);
                $companer->loadFromSQL($company_id);

                $companyApp = new \MultiFlexi\CompanyApp($companer);

                $assigned = [];

                foreach ($companyApp->getAssigned()->select('app_id', true)->fetchAll() as $app) {
                    $assigned[] = $app['app_id'];
                }

                $companyApp->assignApps(array_merge($assigned, [$app_id]));

                $checkData = ['name' => (string) $identifier, 'app_id' => $app_id, 'company_id' => $company_id, 'interv' => 'n'];
                $engine = new \MultiFlexi\RunTemplate($checkData, ['autoload' => false]);

                break;

            default:
                echo $argv[0].' add <name>';

                break;
        }

        $exists = $engine->getColumnsFromSQL(['id'], $checkData);

        if (empty($exists)) {
            try {
                $engine->dbsync();
            } catch (\PDOException $exc) {
                echo $exc->getTraceAsString();
            }
        } else {
            $engine->addStatusMessage('already exists as '.json_encode($exists));
            $engine->loadFromSQL($exists[0]['id']);
        }

        if (empty($properties) === false) {
            switch (\get_class($engine)) {
                case 'MultiFlexi\RunTemplate':
                    $engine->setEnvironment($properties);

                    break;
                case 'MultiFlexi\Company':
                    $enver = new CompanyEnv($engine->getMyKey());

                    foreach ($properties as $propertyKey => $propertyValue) {
                        $enver->addEnv($propertyKey, $propertyValue);
                    }

                    break;

                default:
                    break;
            }
        }

        echo json_encode($engine->getData())."\n";

        break;
    case 'list':
        switch ($argument) {
            case 'user':
                $engine = new \MultiFlexi\User();
                $data = $engine->listingQuery()->select([
                    'id',
                    'enabled',
                    'login',
                    'email',
                    'firstname',
                    'lastname',
                ], true)->fetchAll();

                break;
            case 'app':
                $engine = new Application();
                $data = $engine->listingQuery()->select([
                    'id',
                    'enabled',
                    'image not like "" as image',
                    'name',
                    'description',
                    'executable',
                    'DatCreate',
                    'DatUpdate',
                    'setup',
                    'cmdparams',
                    'deploy',
                    'homepage',
                    'requirements',
                ], true)->fetchAll();

                break;
            case 'company':
                $engine = new Company();
                $data = $engine->listingQuery()->select([
                    'id',
                    'enabled',
                    'settings',
                    'logo  not like "" as logo',
                    'server',
                    'name',
                    'ic',
                    'company',
                    'rw',
                    'setup',
                    'webhook',
                    'DatCreate',
                    'DatUpdate',
                    'customer',
                    'email',
                    'code',
                ])->fetchAll();

                break;
            case 'job':
                $engine = new Job();
                $data = $engine->listingQuery()->select([
                    'id',
                ])->fetchAll();

                break;
            case 'runtemplate':
                $engine = new RunTemplate();
                $data = $engine->listingQuery()->select([
                    'id',
                    'enabled',
                    'name',
                    'app_id',
                    'company_id',
                    'DatCreate',
                    'DatUpdate',
                    'setup',
                    'cmdparams',
                    'deploy',
                    'homepage',
                    'requirements',
                ], true)->fetchAll();

                break;

            default:
                echo "list what ?\n";
                $data = false;

                break;
        }

        if ($data) {
            $table = new \LucidFrame\Console\ConsoleTable();

            foreach (array_keys(current($data))as $column) {
                $table->addHeader($column);
            }

            foreach ($data as $row) {
                $table->addRow($row);
            }

            $table->display();
            // TODO: https://github.com/phplucidframe/console-table/issues/14#issuecomment-2167643219
        } else {
            echo _('No data')."\n";
        }

        break;

    default:
        echo "usage: multiflexi-cli <command> [argument] [id]\n";
        echo "commands: version list remove\n";

        break;
}
