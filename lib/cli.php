<?php

/**
 * Multi Flexi - Cron Scheduled actions executor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2024 Vitex Software
 */

namespace MultiFlexi;

use MultiFlexi\Company;
use Ease\Anonym;
use Ease\Shared;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];
if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST')  && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (\Ease\Shared::cfg('APP_DEBUG') == 'true') {
    $loggers[] = 'console';
}
define('EASE_LOGGER', implode('|', $loggers));
define('APP_NAME', 'MultiFlexi cli');
Shared::user(new Anonym());

$command = array_key_exists(1, $argv) ? $argv[1] : 'help';
$argument = array_key_exists(2, $argv) ? $argv[2] : null;
$identifier = array_key_exists(3, $argv) ? $argv[3] : null;
$property = array_key_exists(4, $argv) ? $argv[4] : null;

switch ($command) {
    case 'version':
        echo \Ease\Shared::appName() . ' ' . \Ease\Shared::appVersion() . "\n";
        break;
    case 'remove':
        switch ($argument) {
            case 'user':
                $engine = new \MultiFlexi\User(intval($identifier));
                break;
            case 'app':
                $engine = new \MultiFlexi\Application(intval($identifier));
                break;
            case 'company':
                $engine = new \MultiFlexi\Company(intval($identifier));
                break;
            default:
                echo $argv[0] . ' remove <sql row id>';
                break;
        }
        $engine->deleteFromSQL();
        break;
    case 'add':
        switch ($argument) {
            case 'user':
                if(empty($identifier)) {
                    echo $argv[0] . " add user <login> <email>";
                    exit;
                }
                $engine = new \MultiFlexi\User(['login' => strval($identifier), 'email' => $property]);
                break;
            case 'app':
                if(empty($identifier)) {
                    echo $argv[0] . " add app <executable> <name>";
                    exit;
                }
                $engine = new \MultiFlexi\Application(['executable' => strval($identifier), 'name' => $property]);
                break;
            case 'company':
                if(empty($identifier)) {
                    echo $argv[0] . " add company <code> <name>";
                    exit;
                }
                $engine = new \MultiFlexi\Company(['code' => strval($identifier), 'name' => $property,'autoload' => 'false']);
                break;

            default:
                echo $argv[0] . ' add <name>';
                break;
        }
        $new = $engine->insertToSQL();
        echo $new."\n";
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
                            "firstname",
                            'lastname',
                                ], true)->fetchAll();
                break;

            case 'app':
                $engine = new Application();
                $data = $engine->listingQuery()->select([
                            'id',
                            'enabled',
                            'image not like "" as image',
                            "name",
                            'description',
                            'executable',
                            'DatCreate',
                            'DatUpdate',
                            'setup',
                            'cmdparams',
                            'deploy',
                            'homepage',
                            'requirements'
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
                            'code'
                        ])->fetchAll();
                break;
            case 'job':
                $engine = new Job();
                $data = $engine->listingQuery()->select([
                            'id',
                        ])->fetchAll();
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
        } else {
            echo _('No data') . "\n";
        }

        break;

    default:
        echo "usage: multiflexi-cli <command> [argument] [id]\n";
        echo "commands: version list remove\n";
        break;
}
