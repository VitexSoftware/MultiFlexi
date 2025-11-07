<?php

declare(strict_types=1);

/**
 * Debug endpoint to test cron value passing
 * This file is part of the MultiFlexi package
 *
 * https://multiflexi.eu/
 *
 * (c) Vítězslav Dvořák <http://vitexsoftware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MultiFlexi\Ui;

// Define bypass for CSRF protection for debugging
define('BYPASS_CSRF_PROTECTION', true);

require_once './init.php';

// Only allow for logged in users
WebPage::singleton()->onlyForLogged();

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Debug: log all received data
$debugData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'request_method' => $_SERVER['REQUEST_METHOD'] ?? 'unknown',
    'post_data' => $_POST,
    'get_data' => $_GET,
    'request_data' => $_REQUEST,
    'raw_input' => file_get_contents('php://input'),
    'headers' => getallheaders(),
];

// Log to file for debugging (if debug is enabled)
if (\Ease\Shared::cfg('APP_DEBUG', false)) {
    $logFile = '/tmp/multiflexi_cron_debug.log';
    file_put_contents($logFile, json_encode($debugData, JSON_PRETTY_PRINT) . "\n\n", FILE_APPEND);
}

// Return the debug data as JSON
echo json_encode([
    'success' => true,
    'debug' => $debugData,
    'received_cron_value' => $_REQUEST['cron'] ?? 'NO_CRON_VALUE',
    'received_runtemplate' => $_REQUEST['runtemplate'] ?? 'NO_RUNTEMPLATE'
]);