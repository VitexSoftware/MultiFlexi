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
 * POST /api/VitexSoftware/MultiFlexi/1.0.0/runtemplates/bulk-execute
 * 
 * Request body:
 * {
 *   "runtemplate_ids": [1, 2, 3],
 *   "when": "now",
 *   "executor": "Native"
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
$executor = $_POST['executor'] ?? 'Native';
$when = $_POST['when'] ?? 'now';

// Validate inputs
if (empty($runtemplateIds) || !\is_array($runtemplateIds)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid or missing runtemplate_ids']);
    exit;
}

try {
    $scheduled = 0;
    $errors = [];
    $jobIds = [];
    
    foreach ($runtemplateIds as $rtId) {
        $rtId = (int) $rtId;
        
        // Load RunTemplate
        $runTemplate = new \MultiFlexi\RunTemplate($rtId);
        
        if (!$runTemplate->getMyKey()) {
            $errors[] = "RunTemplate #{$rtId} not found";
            continue;
        }
        
        // Check if RunTemplate is active
        if (!$runTemplate->getDataValue('active')) {
            $errors[] = "RunTemplate #{$rtId} is not active";
            continue;
        }
        
        // Prepare and schedule job
        $jobber = new \MultiFlexi\Job();
        
        try {
            $whenDateTime = new \DateTime($when);
            $prepared = $jobber->prepareJob(
                $runTemplate->getMyKey(),
                null, // No upload environment for bulk execute
                $whenDateTime,
                $executor,
                'adhoc'
            );
            
            if ($prepared) {
                $scheduled++;
                $jobIds[] = $jobber->getMyKey();
            } else {
                $errors[] = "Failed to schedule RunTemplate #{$rtId}";
            }
        } catch (\Exception $e) {
            $errors[] = "RunTemplate #{$rtId}: ".$e->getMessage();
        }
    }
    
    if ($scheduled > 0) {
        echo json_encode([
            'success' => true,
            'scheduled' => $scheduled,
            'job_ids' => $jobIds,
            'errors' => $errors,
            'message' => sprintf(_('Successfully scheduled %d job(s)'), $scheduled),
        ]);
    } else {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => _('No jobs were scheduled'),
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
