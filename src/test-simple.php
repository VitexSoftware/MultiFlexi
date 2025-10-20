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

echo 'Testing consent functionality...'.\PHP_EOL;

// Test 1: Clear session and simulate first visit
session_write_close();
session_id('test_first_visit_'.time());
session_start();

echo 'Session ID: '.session_id().\PHP_EOL;

require_once 'init.php';

$consentManager = new \MultiFlexi\Consent\ConsentManager();
$consent = $consentManager->getAllConsentStatuses();

if (empty($consent)) {
    echo '✅ PASS: No consent found for new session - banner should show'.\PHP_EOL;
} else {
    echo '❌ FAIL: Consent found for new session - banner should not show yet'.\PHP_EOL;
    print_r($consent);
}

// Test 2: Record consent
echo \PHP_EOL.'Testing consent recording...'.\PHP_EOL;
$success = $consentManager->recordConsent('analytics', true);

if ($success) {
    echo '✅ PASS: Consent recorded successfully'.\PHP_EOL;

    // Check if consent is now found
    $newConsent = $consentManager->getAllConsentStatuses();

    if (!empty($newConsent)) {
        echo '✅ PASS: Consent now found after recording'.\PHP_EOL;
        print_r($newConsent);
    } else {
        echo '❌ FAIL: Consent not found after recording'.\PHP_EOL;
    }
} else {
    echo '❌ FAIL: Could not record consent'.\PHP_EOL;
}
