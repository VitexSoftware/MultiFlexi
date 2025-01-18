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

if (Shared::cfg('ZABBIX_SERVER') && Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

if (Shared::cfg('APP_DEBUG') === 'true') {
    $loggers[] = 'console';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexi cli');
Shared::user(new Anonym());

// Parse command line arguments
$command = $argv[1] ?? null;
$argument = $argv[2] ?? null;
$identifier = $argv[3] ?? null;
$property = $argv[4] ?? null;
$format = 'plain'; // Default format

// Parse options
for ($i = 1; $i < \count($argv); ++$i) {
    if (strpos($argv[$i], '--') === 0) {
        $probBegin = $i;
        break;
    }
}

if (isset($probBegin)) {
    for ($i = $probBegin; $i < \count($argv); ++$i) {
        if (strpos($argv[$i], '--') === 0) {
            $key = substr($argv[$i], 2);
            $value = \array_key_exists($i + 1, $argv) ? $argv[$i + 1] : null;
            if ($key === 'format') {
                $format = $value ?? 'plain';
            } else {
                $properties[$key] = $value;
            }
            ++$i; // Skip the next argument as it is the value
        }
    }
}

switch ($command) {
    case 'version':
        echo Shared::appName().' '.Shared::appVersion().\PHP_EOL;
        break;

    case 'remove':
        switch ($argument) {
            case 'user':
                $engine = new \MultiFlexi\User(is_numeric($identifier) ? (int) $identifier : $identifier);
                break;
            case 'app':
                $engine = new \MultiFlexi\Application((int) $identifier);
                break;
            case 'company':
                $engine = new \MultiFlexi\Company(is_numeric($identifier) ? (int) $identifier : ['code' => $identifier], ['autoload' => 'true']);
                break;
            case 'runtemplate':
                $engine = new \MultiFlexi\RunTemplate((int) $identifier);
                break;
            case 'job':
                $engine = new \MultiFlexi\Job((int) $identifier);
                break;
        }
        break;

    case 'status':
        $engine = new \MultiFlexi\Engine();
        $pdo = $engine->getPdo();
        $database = $pdo->getAttribute(\PDO::ATTR_DRIVER_NAME).' '.
                    $pdo->getAttribute(\PDO::ATTR_CONNECTION_STATUS).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_INFO).' '.
                    $pdo->getAttribute(\PDO::ATTR_SERVER_VERSION);

        $status = [
            'version' => Shared::appVersion(),
            'php' => \PHP_VERSION,
            'os' => \PHP_OS,
            'memory' => memory_get_usage(),
            'companies' => $engine->getFluentPDO()->from('company')->count(),
            'apps' => $engine->getFluentPDO()->from('apps')->count(),
            'runtemplates' => $engine->getFluentPDO()->from('runtemplate')->count(),
            'topics' => $engine->getFluentPDO()->from('topic')->count(),
            'credentials' => $engine->getFluentPDO()->from('credentials')->count(),
            'credential_types' => $engine->getFluentPDO()->from('credential_type')->count(),
            'database' => $database,
            'daemon' => \MultiFlexi\Runner::isServiceActive('multiflexi.service') ? 'running' : 'stopped',
            'timestamp' => date('c')
        ];

        if ($argument === 'jobs') {
            $queeLength = (new \MultiFlexi\Scheduler())->listingQuery()->count();

            // Query to get job status information
            $query = <<<'EOD'
                SELECT
                    COUNT(*) AS total_jobs,
                    SUM(CASE WHEN exitcode = 0 THEN 1 ELSE 0 END) AS successful_jobs,
                    SUM(CASE WHEN exitcode != 0 THEN 1 ELSE 0 END) AS failed_jobs,
                    SUM(CASE WHEN exitcode IS NULL THEN 1 ELSE 0 END) AS incomplete_jobs,
                    COUNT(DISTINCT app_id) AS total_applications,
                    SUM(CASE WHEN schedule IS NOT NULL THEN 1 ELSE 0 END) AS repeated_jobs
                FROM job
EOD;

            $stmt = $pdo->query($query);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            $status = array_merge($status, [
                'successful_jobs' => (int) $result['successful_jobs'],
                'failed_jobs' => (int) $result['failed_jobs'],
                'incomplete_jobs' => (int) $result['incomplete_jobs'],
                'total_applications' => (int) $result['total_applications'],
                'repeated_jobs' => (int) $result['repeated_jobs'],
                'total_jobs' => (int) $result['total_jobs'],
                'quee_length' => (int) $queeLength,
            ]);
        }

        if ($format === 'json') {
            echo json_encode($status, JSON_PRETTY_PRINT) . \PHP_EOL;
        } else {
            foreach ($status as $key => $value) {
                echo ucfirst(str_replace('_', ' ', $key)) . ': ' . $value . \PHP_EOL;
            }
        }
        break;

    default:
        echo "Unknown command: $command" . \PHP_EOL;
        break;
}
