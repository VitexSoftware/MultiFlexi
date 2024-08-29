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

require_once \dirname(__DIR__).'/vendor/autoload.php';
session_start();
\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \dirname(__DIR__).'/.env',
);
\Ease\Locale::singleton(null, '../i18n', 'multiflexi');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (\Ease\Shared::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));

\Ease\Shared::user(null, '\MultiFlexi\User');

/**
 * @global WebPage $oPage
 */
$oPage = new MultiFlexi\Ui\WebPage();
