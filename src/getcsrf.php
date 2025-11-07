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

namespace MultiFlexi\Ui;

// Define bypass for CSRF protection since this endpoint provides CSRF tokens
define('BYPASS_CSRF_PROTECTION', true);

require_once './init.php';

// Only allow for logged in users
WebPage::singleton()->onlyForLogged();

// Set headers for JSON response
header('Content-Type: application/json');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Generate a new CSRF token
$csrfToken = '';
if (isset($GLOBALS['csrfProtection'])) {
    $csrfToken = $GLOBALS['csrfProtection']->generateToken();
}

// Return the token as JSON
echo json_encode([
    'csrf_token' => $csrfToken,
    'success' => true
]);