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

use Ease\Shared;
use MultiFlexi\Ui\WebPage;

require_once '../vendor/autoload.php';
session_start();
Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \dirname(__DIR__).'/.env',
);
\Ease\Locale::singleton(null, '../i18n', 'multiflexi');
$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));

Shared::user(null, '\MultiFlexi\User');

/**
 * @global WebPage $oPage
 */
$oPage = new WebPage('');
WebPage::singleton($oPage);

date_default_timezone_set('Europe/Prague');

$script_tz = date_default_timezone_get();

if (strcmp($script_tz, \ini_get('date.timezone'))) {
    //    echo 'Script timezone differs from ini-set timezone.';
}
//    echo 'Script timezone and ini-set timezone match.';

// Example for OTLP exporter
// filepath: /home/vitex/Projects/Multi/MultiFlexi/src/init.php
use OpenTelemetry\SDK\Trace\TracerProvider;
use OpenTelemetry\SDK\Trace\SpanProcessor\SimpleSpanProcessor;
use OpenTelemetry\Contrib\Otlp\Exporter as OtlpExporter;

$exporter = new OtlpExporter();
$tracerProvider = new TracerProvider(
    new SimpleSpanProcessor($exporter)
);
$tracer = $tracerProvider->getTracer('multiflexi');
