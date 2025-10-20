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

use MultiFlexi\DataExport\UserDataExporter;
use MultiFlexi\Security\DataExportSecurityManager;

require_once './init.php';

// Check if request has valid token
if (!isset($_GET['token'])) {
    http_response_code(400);
    die('Invalid request - missing token');
}

$token = $_GET['token'];
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

// Verify user is authenticated
$currentUser = \Ease\Shared::user();
if (!$currentUser || !$currentUser->getUserID()) {
    http_response_code(403);
    die('Authentication required');
}

$userId = (int) $currentUser->getUserID();

// Verify token using security manager
$securityManager = new DataExportSecurityManager();
$tokenData = $securityManager->verifyDownloadToken($token, $userId, $ipAddress);

if (!$tokenData) {
    http_response_code(403);
    die('Invalid or expired token');
}

$format = $tokenData['format'];

try {
    // Generate the export
    $exporter = new UserDataExporter();
    $exportData = $exporter->exportUserData($userId);
    
    // Get user info for filename
    $userInfo = $exportData['user_profile'];
    $username = $userInfo['username'] ?? 'user';
    $exportDate = date('Y-m-d_H-i-s');
    
    if ($format === 'json') {
        // JSON export
        $filename = "multiflexi_data_export_{$username}_{$exportDate}.json";
        $content = json_encode($exportData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        $mimeType = 'application/json';
        
    } elseif ($format === 'pdf') {
        // PDF export
        $filename = "multiflexi_data_export_{$username}_{$exportDate}.txt";
        $content = $exporter->generatePDF($exportData);
        $mimeType = 'text/plain'; // Simple text format for now
        
    } else {
        http_response_code(400);
        die('Invalid format');
    }
    
    // Log the download
    logDataDownload($userId, $format);
    
    // Send download confirmation email
    $notifier = new \MultiFlexi\Email\DataExportNotifier();
    $notifier->sendExportDownloadedNotification($userId, $format, date('Y-m-d H:i:s'));
    
    // Send file
    header('Content-Type: ' . $mimeType);
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($content));
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    
    echo $content;
    
} catch (\Exception $e) {
    error_log('Data export download error: ' . $e->getMessage());
    http_response_code(500);
    die('Failed to generate export');
}


/**
 * Log data download for audit
 * 
 * @param int $userId
 * @param string $format
 */
function logDataDownload(int $userId, string $format): void
{
    $logEngine = new \Ease\SQL\Engine();
    $logEngine->myTable = 'log';
    
    $logEngine->insertToSQL([
        'user_id' => $userId,
        'severity' => 'info',
        'venue' => 'DataExportDownload',
        'message' => "User downloaded data export in {$format} format from IP " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'),
        'created' => date('Y-m-d H:i:s')
    ]);
}