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
\define('APP_NAME', 'MultiFlexi trigger');
Shared::user(new Anonym());

$shortopts = '';
$shortopts .= 'r::';  // Runtemplate ID or Name
$shortopts .= 'c:';  // Required value Company Code
$shortopts .= 's::'; // Shedule time
$shortopts .= 'e::'; // Environment
$shortopts .= 'v'; // Optional value Verbose
$shortopts .= 'f'; // These options do not accept values
$shortopts .= 'x'; // Executor

$longopts = [
    'runtemplate:',
    'company:', // Required value
    'environment::', // Optional value
    'schedule::', // Optional value
    'foreground', // Optional value
    'verbose', // No value
    'executor::',
];
$options = getopt($shortopts, $longopts);

$jobber = new Job();

if (Shared::cfg('APP_DEBUG', \array_key_exists('v', $options))) {
    $jobber->logBanner();
}

if (isset($options['runtemplate'], $options['company'])) {
    $runTemplater = new \MultiFlexi\RunTemplate(is_numeric($options['runtemplate']) ? (int) $options['runtemplate'] : $options['runtemplate']);

    $app = $runTemplater->getApplication();
    $company = $runTemplater->getCompany();

    $when = $options['schedule'] ?? 'now';
    $uploadEnv = [];

    /**
     * Save all uploaded files into temporary directory and prepare job environment.
     */
    // if (!empty($_FILES)) {
    //     foreach ($_FILES as $field => $file) {
    //         if ($file['error'] === 0) {
    //             $tmpName = tempnam(sys_get_temp_dir(), 'multiflexi_').'_'.basename($file['name']);

    //             if (move_uploaded_file($file['tmp_name'], $tmpName)) {
    //                 $uploadEnv[$field]['value'] = $tmpName;
    //                 $uploadEnv[$field]['type'] = 'file';
    //                 $uploadEnv[$field]['source'] = 'Upload';
    //             }
    //         }
    //     }
    // }
    $uploadEnv = $options['environment'] ?? [];

    $prepared = $jobber->prepareJob($runTemplater->getMyKey(), $uploadEnv, '', $options['executor'] ?? 'Native');
    $jobber->scheduleJobRun(new \DateTime($when));
} else {
    echo "Arguments: \n".
    "--runtemplate - id/name\n".
    "--company COMPANY_CODE - required\n".
    "--environment {json}\n".
    "--schedule - A date/time string for \\DateTime()  \n".
    "--foreground - do not schdule for background run\n".
    "--verbose - more debug messages \n";

    exit(1);
}
