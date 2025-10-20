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

use MultiFlexi\Consent\ConsentManager;

require_once 'init.php';

header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');

// Check if request is AJAX
if (empty($_SERVER['HTTP_X_REQUESTED_WITH']) || strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) !== 'xmlhttprequest') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid request']);

        exit;
    }
}

$consentManager = new ConsentManager();

// Get action from JSON body for POST requests, or from query string for GET requests
$action = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $action = $input['action'] ?? $_POST['action'] ?? '';
} else {
    $action = $_GET['action'] ?? '';
}

try {
    switch ($action) {
        case 'get_consent':
            handleGetConsent($consentManager);

            break;
        case 'save_consent':
            handleSaveConsent($consentManager);

            break;
        case 'withdraw_consent':
            handleWithdrawConsent($consentManager);

            break;
        case 'get_statistics':
            handleGetStatistics($consentManager);

            break;

        default:
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Invalid action']);

            break;
    }
} catch (Exception $e) {
    error_log('Consent API Error: '.$e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Internal server error']);
}

/**
 * Handle getting current consent status.
 */
function handleGetConsent(ConsentManager $consentManager): void
{
    $consent = $consentManager->getAllConsentStatuses();

    echo json_encode([
        'success' => true,
        'consent' => $consent,
    ]);
}

/**
 * Handle saving consent preferences.
 */
function handleSaveConsent(ConsentManager $consentManager): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);

        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['consent']) || !\is_array($input['consent'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid consent data']);

        return;
    }

    $consentData = $input['consent'];
    $version = $input['version'] ?? '1.0';
    $success = true;
    $errors = [];

    // Save each consent type
    foreach ($consentData as $type => $status) {
        if (!\is_bool($status)) {
            continue; // Skip invalid statuses
        }

        if (!$consentManager->recordConsent($type, $status, null, null, null, $version)) {
            $success = false;
            $errors[] = "Failed to save consent for {$type}";
        }
    }

    if ($success) {
        echo json_encode([
            'success' => true,
            'message' => _('Consent preferences saved successfully'),
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => _('Failed to save some consent preferences'),
            'errors' => $errors,
        ]);
    }
}

/**
 * Handle withdrawing specific consent.
 */
function handleWithdrawConsent(ConsentManager $consentManager): void
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['success' => false, 'message' => 'Method not allowed']);

        return;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    if (!$input || !isset($input['consent_type'])) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Missing consent type']);

        return;
    }

    $consentType = $input['consent_type'];

    if ($consentManager->withdrawConsent($consentType)) {
        echo json_encode([
            'success' => true,
            'message' => sprintf(_('Consent for %s has been withdrawn'), $consentType),
        ]);
    } else {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => _('Failed to withdraw consent'),
        ]);
    }
}

/**
 * Handle getting consent statistics (admin only).
 */
function handleGetStatistics(ConsentManager $consentManager): void
{
    $user = \Ease\Shared::user();

    // Check if user is admin (you may need to adjust this based on your user system)
    if (!$user || !$user->getUserID() || !$user->getSettingValue('admin')) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Access denied']);

        return;
    }

    $statistics = $consentManager->getConsentStatistics();

    echo json_encode([
        'success' => true,
        'statistics' => $statistics,
    ]);
}
