<?php

declare(strict_types=1);

/**
 * JSON to Credential Type Importer
 *
 * This script imports credential type definitions from JSON files that conform
 * to the MultiFlexi credential type schema into the database.
 *
 * Usage:
 *   php json2credential-type.php <path-to-json-file>
 *   php json2credential-type.php /path/to/credential-type.json
 *
 * Example:
 *   php json2credential-type.php tests/probe.multiflexi.credential-type.json
 */

// Include the MultiFlexi initialization
require_once '../vendor/autoload.php';

\Ease\Shared::init(['DB_CONNECTION', 'DB_HOST', 'DB_PORT', 'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD'], '../.env');
$loggers = ['console', 'syslog', '\MultiFlexi\LogToSQL'];

if (\Ease\Shared::cfg('ZABBIX_SERVER') && \Ease\Shared::cfg('ZABBIX_HOST') && class_exists('\MultiFlexi\LogToZabbix')) {
    $loggers[] = '\MultiFlexi\LogToZabbix';
}

\define('EASE_LOGGER', implode('|', $loggers));
\define('APP_NAME', 'MultiFlexi json2credential-type');

/**
 * Display usage information
 */
function showUsage(): void
{
    echo "Usage: php json2credential-type.php <path-to-json-file>\n";
    echo "\n";
    echo "This script imports credential type definitions from JSON files\n";
    echo "that conform to the MultiFlexi credential type schema.\n";
    echo "\n";
    echo "Example:\n";
    echo "  php json2credential-type.php tests/probe.multiflexi.credential-type.json\n";
    echo "\n";
    echo "The JSON file must contain:\n";
    echo "  - uuid: Unique identifier for the credential type\n";
    echo "  - code: Short code identifier\n";
    echo "  - name: Name (can be localized object or simple string)\n";
    echo "  - description: Description (can be localized object or simple string)\n";
    echo "  - fields: Array of field definitions\n";
    echo "\n";
}

/**
 * Validate JSON file against schema (basic validation)
 */
function validateCredentialTypeJson(array $data): array
{
    $errors = [];
    
    // Required fields
    $requiredFields = ['uuid', 'code', 'name', 'description', 'fields'];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $errors[] = "Missing required field: {$field}";
        }
    }
    
    // Validate UUID format
    if (isset($data['uuid']) && !preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $data['uuid'])) {
        $errors[] = "Invalid UUID format: {$data['uuid']}";
    }
    
    // Validate fields array
    if (isset($data['fields']) && is_array($data['fields'])) {
        foreach ($data['fields'] as $index => $field) {
            if (!is_array($field)) {
                $errors[] = "Field at index {$index} must be an object";
                continue;
            }
            
            $requiredFieldKeys = ['keyword', 'name', 'type'];
            foreach ($requiredFieldKeys as $key) {
                if (!isset($field[$key])) {
                    $errors[] = "Field at index {$index} missing required key: {$key}";
                }
            }
            
            // Validate field type
            $validTypes = ['string', 'number', 'boolean', 'secret', 'text', 'url', 'email'];
            if (isset($field['type']) && !in_array($field['type'], $validTypes, true)) {
                $fieldName = $field['keyword'] ?? $index;
                $errors[] = "Field '{$fieldName}' has invalid type: {$field['type']}. Valid types: " . implode(', ', $validTypes);
            }
        }
    }
    
    return $errors;
}

/**
 * Display credential type information
 */
function displayCredentialTypeInfo(array $data): void
{
    echo "Credential Type Information:\n";
    echo "============================\n";
    echo "UUID: {$data['uuid']}\n";
    echo "Code: {$data['code']}\n";
    echo "Version: " . ($data['version'] ?? 'N/A') . "\n";
    
    // Display name
    if (is_array($data['name'])) {
        echo "Name:\n";
        foreach ($data['name'] as $lang => $name) {
            echo "  {$lang}: {$name}\n";
        }
    } else {
        echo "Name: {$data['name']}\n";
    }
    
    // Display description
    if (is_array($data['description'])) {
        echo "Description:\n";
        foreach ($data['description'] as $lang => $desc) {
            echo "  {$lang}: {$desc}\n";
        }
    } else {
        echo "Description: {$data['description']}\n";
    }
    
    // Display fields
    if (isset($data['fields']) && is_array($data['fields'])) {
        echo "Fields (" . count($data['fields']) . "):\n";
        foreach ($data['fields'] as $field) {
            echo "  - {$field['keyword']} ({$field['type']})";
            if ($field['required'] ?? false) {
                echo " [REQUIRED]";
            }
            if (isset($field['default'])) {
                echo " [Default: {$field['default']}]";
            }
            echo "\n";
            
            if (is_array($field['name'] ?? null)) {
                echo "    Name: " . ($field['name']['en'] ?? reset($field['name'])) . "\n";
            } elseif (isset($field['name'])) {
                echo "    Name: {$field['name']}\n";
            }
            
            if (is_array($field['description'] ?? null)) {
                echo "    Description: " . ($field['description']['en'] ?? reset($field['description'])) . "\n";
            } elseif (isset($field['description'])) {
                echo "    Description: {$field['description']}\n";
            }
        }
    }
    echo "\n";
}

// Main execution
try {
    // Check command line arguments
    if ($argc < 2 || in_array($argv[1], ['--help', '-h', 'help'])) {
        if ($argc >= 2) {
            echo "JSON to Credential Type Importer\n\n";
        } else {
            echo "Error: Missing JSON file path.\n\n";
        }
        showUsage();
        exit($argc >= 2 ? 0 : 1);
    }
    
    $jsonFile = $argv[1];
    
    // Convert relative path to absolute if needed
    if (!str_starts_with($jsonFile, '/')) {
        // If running from lib directory, adjust the path to project root
        $jsonFile = dirname(__DIR__) . '/' . $jsonFile;
    }
    
    // Check if file exists
    if (!file_exists($jsonFile)) {
        echo "Error: File not found: {$jsonFile}\n";
        exit(1);
    }
    
    echo "JSON to Credential Type Importer\n";
    echo "=================================\n\n";
    echo "Importing from: {$jsonFile}\n\n";
    
    // Read and parse JSON file
    $jsonContent = file_get_contents($jsonFile);
    if ($jsonContent === false) {
        throw new Exception("Cannot read file: {$jsonFile}");
    }
    
    $data = json_decode($jsonContent, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data: ' . json_last_error_msg());
    }
    
    // Validate the JSON structure
    echo "Validating JSON structure...\n";
    $validationErrors = validateCredentialTypeJson($data);
    
    if (!empty($validationErrors)) {
        echo "Validation failed:\n";
        foreach ($validationErrors as $error) {
            echo "  - {$error}\n";
        }
        exit(1);
    }
    
    echo "✓ JSON structure is valid\n\n";
    
    // Display credential type information
    displayCredentialTypeInfo($data);
    
    // Create CredentialType instance and import
    echo "Importing credential type...\n";
    
    $credentialType = new \MultiFlexi\CredentialType();
    
    // Import the credential type
    $result = $credentialType->importCredTypeJson($jsonFile);
    
    if ($result) {
        echo "✓ Successfully imported credential type: {$data['code']}\n";
        echo "  - Database ID: {$credentialType->getMyKey()}\n";
        echo "  - UUID: {$data['uuid']}\n";
        
        // Display field import summary
        if (isset($data['fields']) && is_array($data['fields'])) {
            echo "  - Imported " . count($data['fields']) . " field(s)\n";
        }
        
        echo "\nImport completed successfully!\n";
    } else {
        echo "✗ Failed to import credential type\n";
        exit(1);
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    
    // Add more detailed error information in debug mode
    if (isset($_ENV['DEBUG']) || isset($_SERVER['DEBUG'])) {
        echo "\nStack trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
    
    exit(1);
} catch (Error $e) {
    echo "Fatal error: " . $e->getMessage() . "\n";
    
    if (isset($_ENV['DEBUG']) || isset($_SERVER['DEBUG'])) {
        echo "\nStack trace:\n";
        echo $e->getTraceAsString() . "\n";
    }
    
    exit(1);
}