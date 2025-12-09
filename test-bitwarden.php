<?php

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Create a simple BitwardenServiceDelegate implementation
class TestBitwardenDelegate implements \Jalismrs\Bitwarden\BitwardenServiceDelegate
{
    private string $email;
    private string $password;
    private ?string $url;

    public function __construct(string $email, string $password, ?string $url = null)
    {
        $this->email = $email;
        $this->password = $password;
        $this->url = $url;
    }

    public function getOrganizationId(): ?string
    {
        return null;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function getUserEmail(): string
    {
        return $this->email;
    }

    public function getUserPassword(): string
    {
        return $this->password;
    }

    public function storeSession(string $session): void
    {
        // No-op for testing
    }

    public function restoreSession(): ?string
    {
        return '';
    }
}

// Test the BitwardenStatus class with null URL
try {
    $json = '{"serverUrl":null,"lastSync":null,"status":"unauthenticated"}';
    $status = \Jalismrs\Bitwarden\Model\BitwardenStatus::fromJson($json);
    echo "Success: BitwardenStatus created with null URL\n";
    echo "Status: " . $status->getStatus() . "\n";
    echo "URL: " . ($status->getUrl() === null ? "null" : $status->getUrl()) . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test with URL
try {
    $json = '{"serverUrl":"https://vault.example.com","lastSync":null,"status":"unauthenticated"}';
    $status = \Jalismrs\Bitwarden\Model\BitwardenStatus::fromJson($json);
    echo "Success: BitwardenStatus created with URL\n";
    echo "Status: " . $status->getStatus() . "\n";
    echo "URL: " . $status->getUrl() . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nDone testing BitwardenStatus\n";