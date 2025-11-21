#!/usr/bin/php
<?php

declare(strict_types=1);

/**
 * Test script to debug duplicate rows in CompanyJobLister
 */

require_once __DIR__ . '/vendor/autoload.php';

\Ease\Shared::init(
    ['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'],
    __DIR__ . '/.env'
);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$lister = new \MultiFlexi\CompanyJobLister();

// Get the query object
$query = $lister->listingQuery();

// Apply the same JOINs as in addSelectizeValues
$query->select(['apps.name AS appname', 'apps.uuid', 'apps.image AS appimage', 'job.id', 'begin', 'end', 'exitcode', 'launched_by', 'login', 'job.app_id AS app_id', 'job.executor', 'job.company_id', 'company.name', 'company.logo', 'schedule', 'schedule_type', 'job.runtemplate_id', 'runtemplate.name AS runtemplate_name'], true)
    ->leftJoin('apps ON apps.id = job.app_id')
    ->leftJoin('company ON company.id = job.company_id')
    ->leftJoin('user ON user.id = job.launched_by')
    ->leftJoin('runtemplate ON runtemplate.id = job.runtemplate_id')
    ->orderBy('job.id DESC')
    ->limit(20);

echo "=== Generated SQL Query ===\n";
echo $query->getQuery() . "\n\n";

echo "=== Executing Query ===\n";
$results = $query->fetchAll();

echo "Total rows returned: " . count($results) . "\n\n";

// Check for duplicates
$jobIds = [];
$duplicates = [];

foreach ($results as $row) {
    $jobId = $row['id'];
    if (isset($jobIds[$jobId])) {
        $duplicates[] = $jobId;
        echo "DUPLICATE FOUND: Job ID {$jobId}\n";
    }
    $jobIds[$jobId] = ($jobIds[$jobId] ?? 0) + 1;
}

if (empty($duplicates)) {
    echo "✓ No duplicates found in result set\n";
} else {
    echo "\n❌ Duplicates detected:\n";
    foreach ($jobIds as $jobId => $count) {
        if ($count > 1) {
            echo "  Job ID {$jobId}: appears {$count} times\n";
        }
    }
}

echo "\n=== Sample of first 5 rows ===\n";
foreach (array_slice($results, 0, 5) as $idx => $row) {
    echo "Row " . ($idx + 1) . ": Job ID = {$row['id']}, App = {$row['appname']}, Company = {$row['name']}\n";
}
