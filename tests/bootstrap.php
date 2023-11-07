<?php
/**
 * Multi Flexi - Unit Tests Bootstrap
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 * @copyright  2023 Vitex Software
 */

require_once './vendor/autoload.php';
session_start();
\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    dirname(__DIR__) . '/.env'
);
\Ease\Locale::singleton(null, '../i18n', 'multiflexi');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];
if (\Ease\Functions::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

define('EASE_LOGGER', implode('|', $loggers));

\Ease\Shared::user(null, '\MultiFlexi\User');

/**
 * @global WebPage $oPage
 */
$oPage = new MultiFlexi\Ui\WebPage();
