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

require_once '../vendor/autoload.php';

\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];

if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexi json app Remover');

\Ease\Shared::user(new \Ease\Anonym());

if (\array_key_exists('1', $argv) && file_exists($argv[1])) {
    $apper = new Application($argc === 3 ? (int) ($argv[3]) : null);

    if (\Ease\Shared::cfg('APP_DEBUG')) {
        $apper->logBanner();
    }

    if (empty($apper->jsonAppRemove($argv[1]))) {
        $apper->addStatusMesssage(_('Error importing application json'), 'error');

        exit(1);
    }
} else {
    echo 'usage: app.template.json [app id]';
}
