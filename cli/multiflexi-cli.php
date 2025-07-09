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

namespace MultiFlexi\Cli;

require_once __DIR__.'/../vendor/autoload.php';

use Ease\Anonym;
use Ease\Shared;
use MultiFlexi\Cli\Command\ApplicationCommand;
use MultiFlexi\Cli\Command\AppStatusCommand;
use MultiFlexi\Cli\Command\CompanyAppCommand;
use MultiFlexi\Cli\Command\CompanyCommand;
use MultiFlexi\Cli\Command\CredentialTypeCommand;
use MultiFlexi\Cli\Command\DescribeCommand;
use MultiFlexi\Cli\Command\JobCommand;
use MultiFlexi\Cli\Command\PruneCommand;
use MultiFlexi\Cli\Command\QueueCommand;
use MultiFlexi\Cli\Command\RunTemplateCommand;
use MultiFlexi\Cli\Command\TokenCommand;
use MultiFlexi\Cli\Command\UserCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\CompleteCommand;
use Symfony\Component\Console\Input\InputOption;

$globalOptions = getopt('e::', ['environment::']);

Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \array_key_exists('environment', $globalOptions) ? $globalOptions['environment'] : (\array_key_exists('e', $globalOptions) ? $globalOptions['e'] : '../.env'),
);

$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (Shared::cfg('APP_DEBUG') === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexiCLI');

Shared::user(new Anonym());

$application = new Application(Shared::appName(), Shared::appVersion());

$application->add(new JobCommand());
$application->add(new CompanyCommand());
$application->add(new TokenCommand());
$application->add(new RunTemplateCommand());
$application->add(new UserCommand());
$application->add(new ApplicationCommand());
$application->add(new CredentialTypeCommand());
$application->add(new DescribeCommand());
$application->add(new CompleteCommand());
$application->add(new AppStatusCommand());
$application->add(new CompanyAppCommand());
$application->add(new QueueCommand());
$application->add(new PruneCommand());
$application->getDefinition()->addOption(new InputOption('fields', null, InputOption::VALUE_OPTIONAL, 'Comma-separated list of fields to display'));
$application->run();
