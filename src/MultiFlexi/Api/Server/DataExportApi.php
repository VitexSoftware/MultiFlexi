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

namespace MultiFlexi\Api\Server;

use MultiFlexi\DataExport\UserDataExporter;
use MultiFlexi\Email\DataExportNotifier;
use MultiFlexi\Security\DataExportSecurityManager;

/**
 * GDPR Data Export API Controller.
 *
 * Implements Article 15 - Right of Access under GDPR
 * Allows users to export all their personal data in structured formats
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.cz>
 */
class DataExportApi
{
    private UserDataExporter $exporter;
    private DataExportSecurityManager $securityManager;
    private DataExportNotifier $notifier;

    public function __construct()
    {
        $this->exporter = new UserDataExporter();
        $this->securityManager = new DataExportSecurityManager();
        $this->notifier = new DataExportNotifier();
    }

    /**
     * Handle data export request.
     */
    public function exportUserData(): void
    {
        header('Content-Type: application/json');

        // Check authentication
        $user = \Ease\Shared::user();

        if (!$user || !$user->getUserID()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);

            return;
        }

        $userId = (int) $user->getUserID();
        $ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

        // Check security permissions
        $securityCheck = $this->securityManager->canRequestExport($userId, $ipAddress);

        if (!$securityCheck['allowed']) {
            $statusCode = match ($securityCheck['reason']) {
                'authentication_required' => 401,
                'rate_limit_exceeded' => 429,
                'suspicious_activity' => 403,
                default => 400,
            };

            http_response_code($statusCode);
            echo json_encode(['error' => $securityCheck['message']]);

            return;
        }

        try {
            // Get format from request
            $format = $_GET['format'] ?? 'json';

            if (!\in_array($format, ['json', 'pdf'], true)) {
                http_response_code(400);
                echo json_encode(['error' => 'Invalid format. Supported formats: json, pdf']);

                return;
            }

            // Generate export (validate data can be exported)
            $exportData = $this->exporter->exportUserData($userId);

            // Create secure download token
            $tokenResult = $this->securityManager->createSecureToken($userId, $format, $ipAddress, $userAgent);

            if (!$tokenResult['success']) {
                http_response_code(500);
                echo json_encode(['error' => $tokenResult['error'] ?? 'Failed to create secure download link']);

                return;
            }

            // Send email notification
            $downloadUrl = "/data-export.php?token={$tokenResult['token']}";
            $this->notifier->sendExportReadyNotification($userId, $format, $downloadUrl, $tokenResult['expires_at']);

            // Return download URL
            echo json_encode([
                'success' => true,
                'download_url' => $downloadUrl,
                'format' => $format,
                'generated_at' => date('c'),
                'expires_at' => $tokenResult['expires_at'],
                'notification_sent' => true,
            ]);
        } catch (\Exception $e) {
            error_log('Data export error: '.$e->getMessage());
            http_response_code(500);
            echo json_encode(['error' => 'Failed to generate data export']);
        }
    }

    /**
     * Get export status.
     */
    public function getExportStatus(): void
    {
        header('Content-Type: application/json');

        $user = \Ease\Shared::user();

        if (!$user || !$user->getUserID()) {
            http_response_code(401);
            echo json_encode(['error' => 'Authentication required']);

            return;
        }

        $userId = (int) $user->getUserID();

        // Get recent export requests from audit log
        $exports = $this->getRecentExports($userId);

        echo json_encode([
            'success' => true,
            'exports' => $exports,
        ]);
    }

    /**
     * Get recent export requests for user.
     */
    private function getRecentExports(int $userId): array
    {
        $logEngine = new \Ease\SQL\Engine();
        $logEngine->myTable = 'log';

        $exports = $logEngine->listingQuery()
            ->select('message, created')
            ->where(['user_id' => $userId, 'venue' => 'DataExportApi'])
            ->orderBy('created DESC')
            ->limit(10)
            ->fetchAll();

        return array_map(static function ($export) {
            return [
                'message' => $export['message'],
                'requested_at' => $export['created'],
            ];
        }, $exports);
    }
}
