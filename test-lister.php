<?php

require_once './src/init.php';

try {
    $lister = new \MultiFlexi\CompanyAppRunTemplateLister();
    $lister->setCompany(1)->setApp(19);
    
    echo "Lister created successfully\n";
    
    $query = $lister->listingQuery();
    echo "Query created successfully\n";
    
    $query = $lister->addSelectizeValues($query);
    echo "SelectizeValues added successfully\n";
    
    echo "SQL: " . $query->getQuery() . "\n\n";
    
    $count = count($query);
    echo "Record count: $count\n";
    
    if ($count > 0) {
        $data = $query->fetch();
        echo "First record:\n";
        print_r($data);
    }
    
    echo "\n\nTest completed successfully!\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
