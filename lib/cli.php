#!/usr/bin/env php
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
use Symfony\Component\Console\Application;

require_once '../vendor/autoload.php';
Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['syslog', '\MultiFlexi\LogToSQL', 'console'];

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (Shared::cfg('APP_DEBUG') === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexiCLI');

Shared::user(new Anonym());

$app = new Application(\Ease\Shared::appName(), \Ease\Shared::appVersion());
$app->add(new Command\AppStatus());
$app->add(new Command\JobStatus());
$app->add(new Command\Application());
$app->add(new Command\Company());
$app->add(new Command\CredentialType());
$app->add(new Command\Job());
$app->add(new Command\User());
$app->add(new Command\RunTemplate());

$app->run();
