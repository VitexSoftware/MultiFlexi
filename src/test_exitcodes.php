<?php

require_once '../vendor/autoload.php';

\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], \dirname(__DIR__).'/.env');

$exitCodesEngine = new \Ease\SQL\Engine();
$exitCodesEngine->takemyTable('app_exit_codes');

try {
    $exitCodesData = $exitCodesEngine->listingQuery()
        ->where('app_id', 3)
        ->orderBy('exit_code')
        ->orderBy('lang')
        ->fetchAll();
    
    echo "Success! Found " . count($exitCodesData) . " exit codes:\n";
    print_r($exitCodesData);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
