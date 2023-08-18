<?php

/**
 * Multi Flexi - Company instance editor.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2020-2022 Vitex Software
 */

namespace AbraFlexi\MultiFlexi;

use Ease\Shared;
use AbraFlexi\MultiFlexi\Ui\WebPage;

require_once '../vendor/autoload.php';
session_start();
\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], dirname(__DIR__) . '/.env');
\Ease\Locale::singleton(null, '../i18n', 'multiflexi');
$loggers = ['syslog', '\AbraFlexi\MultiFlexi\LogToSQL'];
if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\AbraFlexi\MultiFlexi\LogToZabbix';
}

define('EASE_LOGGER', implode('|', $loggers));

/**
 * @global User $oUser
 */
$oUser = Shared::user(null, 'AbraFlexi\MultiFlexi\User');

/**
 * @global WebPage $oPage
 */
$oPage = new WebPage();
