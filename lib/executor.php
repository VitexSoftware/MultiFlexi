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

$options = getopt('r:o::e::', ['output::environment::']);
Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    \array_key_exists('environment', $options) ? $options['environment'] : (\array_key_exists('e', $options) ? $options['e'] : '../.env'),
);
$destination = \array_key_exists('o', $options) ? $options['o'] : (\array_key_exists('output', $options) ? $options['output'] : Shared::cfg('RESULT_FILE', 'php://stdout'));

$runtempateId = (int) (\array_key_exists('r', $options) ? $options['r'] : (\array_key_exists('runtemplate', $options) ? $options['runtemplate'] : Shared::cfg('RUNTEMPLATE_ID', 0)));

$loggers = ['syslog', '\MultiFlexi\LogToSQL'];

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (strtolower(Shared::cfg('APP_DEBUG', 'false')) === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
$interval = $argc === 2 ? $argv[1] : null;
\define('APP_NAME', 'MultiFlexi executor '.RunTemplate::codeToInterval($interval));
Shared::user(new Anonym());

$runTemplater = new \MultiFlexi\RunTemplate($runtempateId);

if (Shared::cfg('APP_DEBUG')) {
    $runTemplater->logBanner();
}

if ($runTemplater->getMyKey()) {
    $jobber = new Job();
    $jobber->prepareJob($runTemplater->getMyKey(), new ConfigFields('empty'), new \DateTime());
    $jobber->performJob();

    echo $jobber->executor->getOutput();

    if ($jobber->executor->getErrorOutput()) {
        fwrite(fopen('php://stderr', 'wb'), $jobber->executor->getErrorOutput().\PHP_EOL);
    }

    exit($jobber->executor->getExitCode());
}

fwrite(fopen('php://stderr', 'wb'), 'Specify runtemplate ID to run (-r)'.\PHP_EOL);
