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

/**
 * TODO: Move to API when POST/PUT support is implemented
 * 
 * This endpoint will be migrated to:
 * POST /api/VitexSoftware/MultiFlexi/1.0.0/runtemplates/bulk-reconfigure
 * 
 * Request body:
 * {
 *   "runtemplate_ids": [1, 2, 3],
 *   "config_key": "ACCOUNT_NUMBER",
 *   "config_value": "123456"
 * }
 */

namespace MultiFlexi\Ui;

require_once './init.php';

header('Content-Type: application/json');

// Check if user is logged in
if (!WebPage::singleton()->isLogged()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Get POST parameters
$runtemplateIds = $_POST['runtemplate_ids'] ?? [];
$configKey = $_POST['config_key'] ?? '';
$configValue = $_POST['config_value'] ?? '';

// Validate inputs
if (empty($runtemplateIds) || !\is_array($runtemplateIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing runtemplate_ids']);
    exit;
}

if (empty($configKey)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing config_key']);
    exit;
}

try {
    $updated = 0;
    $errors = [];
    
    foreach ($runtemplateIds as $rtId) {
        $rtId = (int) $rtId;
        
        // Load RunTemplate
        $runTemplate = new \MultiFlexi\RunTemplate($rtId);
        
        if (!$runTemplate->getMyKey()) {
            $errors[] = "RunTemplate #{$rtId} not found";
            continue;
        }
        
        // Get existing configuration entry or create new
        $envConfig = new \MultiFlexi\Configuration();
        
        $existing = $envConfig->listingQuery()
            ->where('runtemplate_id', $rtId)
            ->where('keyword', $configKey)
            ->fetch();
        
        if ($existing) {
            // Update existing configuration
            $envConfig->setMyKey($existing['id']);
            $envConfig->setDataValue('value', $configValue);
            
            if ($envConfig->dbsync()) {
                $updated++;
            } else {
                $errors[] = "Failed to update RunTemplate #{$rtId}";
            }
        } else {
            // Create new configuration entry
            $envConfig->setDataValue('runtemplate_id', $rtId);
            $envConfig->setDataValue('keyword', $configKey);
            $envConfig->setDataValue('value', $configValue);
            
            if ($envConfig->insertToSQL()) {
                $updated++;
            } else {
                $errors[] = "Failed to create configuration for RunTemplate #{$rtId}";
            }
        }
    }
    
    if ($updated > 0) {
        echo json_encode([
            'success' => true,
            'updated' => $updated,
            'errors' => $errors,
            'message' => sprintf(_('Successfully updated %d RunTemplate(s)'), $updated),
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => _('No RunTemplates were updated'),
            'errors' => $errors,
        ]);
    }
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
    ]);
}
