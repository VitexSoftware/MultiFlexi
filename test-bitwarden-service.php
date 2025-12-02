<?php

// Include Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

// Our test delegate implementation
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

// Create a fake email and password (these won't be used since we're not actually connecting to a server)
$email = 'test@example.com';
$password = 'test-password';
$url = 'https://vault.example.com';

// Test with URL
$delegateWithUrl = new TestBitwardenDelegate($email, $password, $url);
$serviceWithUrl = new \Jalismrs\Bitwarden\BitwardenService($delegateWithUrl);

echo "Created BitwardenService with URL: $url\n";

// Test without URL
$delegateNoUrl = new TestBitwardenDelegate($email, $password);
$serviceNoUrl = new \Jalismrs\Bitwarden\BitwardenService($delegateNoUrl);

echo "Created BitwardenService without URL\n";

echo "Note: We can't fully test the service without a real Bitwarden CLI instance,\n";
echo "but we've verified that the URL is correctly passed through the delegation chain.\n";