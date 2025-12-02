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

// Setup the reflection to see the private properties
class ReflectionHelper
{
    public static function getPrivateProperty($object, $propertyName)
    {
        $reflection = new ReflectionClass($object);
        $property = $reflection->getProperty($propertyName);
        $property->setAccessible(true);
        return $property->getValue($object);
    }

    public static function callPrivateMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass($object);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($object, $parameters);
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
echo "Server configured initially: " . (ReflectionHelper::getPrivateProperty($serviceWithUrl, 'serverConfigured') ? 'yes' : 'no') . "\n";

// Call a method that should trigger server config
try {
    // This will not actually run the command since we're mocking, but it will set serverConfigured to true
    ReflectionHelper::callPrivateMethod($serviceWithUrl, 'configureServerIfNeeded');
    echo "After configureServerIfNeeded: " . (ReflectionHelper::getPrivateProperty($serviceWithUrl, 'serverConfigured') ? 'yes' : 'no') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

// Test without URL
$delegateNoUrl = new TestBitwardenDelegate($email, $password);
$serviceNoUrl = new \Jalismrs\Bitwarden\BitwardenService($delegateNoUrl);

echo "\nCreated BitwardenService without URL\n";
echo "Server configured initially: " . (ReflectionHelper::getPrivateProperty($serviceNoUrl, 'serverConfigured') ? 'yes' : 'no') . "\n";

try {
    ReflectionHelper::callPrivateMethod($serviceNoUrl, 'configureServerIfNeeded');
    echo "After configureServerIfNeeded: " . (ReflectionHelper::getPrivateProperty($serviceNoUrl, 'serverConfigured') ? 'yes' : 'no') . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\nNote: In a real environment, the server configuration would be set using 'bw config server <url>'.\n";