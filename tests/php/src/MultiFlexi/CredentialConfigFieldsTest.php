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

namespace Tests\MultiFlexi;

use MultiFlexi\ConfigField;
use MultiFlexi\ConfigFields;
use MultiFlexi\Credential;
use MultiFlexi\CredentialConfigFields;
use MultiFlexi\CredentialType;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CredentialConfigFields class.
 */
class CredentialConfigFieldsTest extends TestCase
{
    private Credential|\PHPUnit\Framework\MockObject\MockObject $credentialMock;
    private CredentialType|\PHPUnit\Framework\MockObject\MockObject $credentialTypeMock;

    protected function setUp(): void
    {
        // Create mock for Credential
        $this->credentialMock = $this->createMock(Credential::class);

        // Create mock for CredentialType
        $this->credentialTypeMock = $this->createMock(CredentialType::class);

        // Setup credential mock to return credential type mock
        $this->credentialMock->method('getCredentialType')
            ->willReturn($this->credentialTypeMock);

        // Setup credential mock to return an ID
        $this->credentialMock->method('getId')
            ->willReturn(42);

        // Setup credential mock to return a name
        $this->credentialMock->method('getName')
            ->willReturn('Test Credential');
    }

    /**
     * Test constructor with credential.
     */
    public function testConstructWithCredential(): void
    {
        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        $this->assertInstanceOf(ConfigFields::class, $credentialConfigFields);
        $this->assertInstanceOf(CredentialConfigFields::class, $credentialConfigFields);

        // Test if name is set correctly from credential
        $this->assertEquals('Test Credential', $credentialConfigFields->getName());
    }

    /**
     * Test constructor without credential.
     */
    public function testConstructWithoutCredential(): void
    {
        $credentialConfigFields = new CredentialConfigFields();

        $this->assertInstanceOf(ConfigFields::class, $credentialConfigFields);
        $this->assertInstanceOf(CredentialConfigFields::class, $credentialConfigFields);

        // Empty name since no credential was provided
        $this->assertEquals('', $credentialConfigFields->getName());
    }

    /**
     * Test getCredential method.
     */
    public function testGetCredential(): void
    {
        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        $this->assertSame($this->credentialMock, $credentialConfigFields->getCredential());
    }

    /**
     * Test setCredential method.
     */
    public function testSetCredential(): void
    {
        $credentialConfigFields = new CredentialConfigFields();

        // Initially no credential
        $this->assertNull($credentialConfigFields->getCredential());

        // Set credential
        $result = $credentialConfigFields->setCredential($this->credentialMock);

        // Test fluent interface
        $this->assertSame($credentialConfigFields, $result);

        // Test credential is set
        $this->assertSame($this->credentialMock, $credentialConfigFields->getCredential());

        // Test name is updated from credential
        $this->assertEquals('Test Credential', $credentialConfigFields->getName());
    }

    /**
     * Test addFieldsFromCredential method.
     */
    public function testAddFieldsFromCredential(): void
    {
        // Setup credential mock to return configuration fields
        $configData = [
            ['k' => 'api_key', 'v' => 'test-key', 't' => 'string'],
            ['k' => 'api_url', 'v' => 'https://example.com', 't' => 'url'],
        ];

        $this->credentialMock->method('getConfiguration')
            ->willReturn($configData);

        // Create instance with credential
        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Add fields from credential
        $result = $credentialConfigFields->addFieldsFromCredential();

        // Test fluent interface
        $this->assertSame($credentialConfigFields, $result);

        // Test fields were added
        $this->assertCount(2, $credentialConfigFields->getFields());

        // Test field content
        $apiKeyField = $credentialConfigFields->getField('api_key');
        $this->assertInstanceOf(ConfigField::class, $apiKeyField);
        $this->assertEquals('test-key', $apiKeyField->getValue());
        $this->assertEquals('string', $apiKeyField->getType());

        $apiUrlField = $credentialConfigFields->getField('api_url');
        $this->assertInstanceOf(ConfigField::class, $apiUrlField);
        $this->assertEquals('https://example.com', $apiUrlField->getValue());
        $this->assertEquals('url', $apiUrlField->getType());
    }

    /**
     * Test addFieldsFromCredential with empty configuration.
     */
    public function testAddFieldsFromCredentialEmpty(): void
    {
        // Setup credential mock to return empty configuration
        $this->credentialMock->method('getConfiguration')
            ->willReturn([]);

        // Create instance with credential
        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Add fields from credential
        $credentialConfigFields->addFieldsFromCredential();

        // Test no fields were added
        $this->assertCount(0, $credentialConfigFields->getFields());
    }

    /**
     * Test loadFromArray method.
     */
    public function testLoadFromArray(): void
    {
        $configData = [
            'username' => ['value' => 'testuser', 'type' => 'string'],
            'password' => ['value' => 'secret', 'type' => 'password'],
        ];

        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Load fields from array
        $result = $credentialConfigFields->loadFromArray($configData);

        // Test fluent interface
        $this->assertSame($credentialConfigFields, $result);

        // Test fields were added
        $this->assertCount(2, $credentialConfigFields->getFields());

        // Test field content
        $usernameField = $credentialConfigFields->getField('username');
        $this->assertInstanceOf(ConfigField::class, $usernameField);
        $this->assertEquals('testuser', $usernameField->getValue());
        $this->assertEquals('string', $usernameField->getType());

        $passwordField = $credentialConfigFields->getField('password');
        $this->assertInstanceOf(ConfigField::class, $passwordField);
        $this->assertEquals('secret', $passwordField->getValue());
        $this->assertEquals('password', $passwordField->getType());
    }

    /**
     * Test loadFromJson method.
     */
    public function testLoadFromJson(): void
    {
        $jsonData = json_encode([
            'username' => ['value' => 'testuser', 'type' => 'string'],
            'password' => ['value' => 'secret', 'type' => 'password'],
        ]);

        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Load fields from JSON
        $result = $credentialConfigFields->loadFromJson($jsonData);

        // Test fluent interface
        $this->assertSame($credentialConfigFields, $result);

        // Test fields were added
        $this->assertCount(2, $credentialConfigFields->getFields());

        // Test field content
        $usernameField = $credentialConfigFields->getField('username');
        $this->assertInstanceOf(ConfigField::class, $usernameField);
        $this->assertEquals('testuser', $usernameField->getValue());

        $passwordField = $credentialConfigFields->getField('password');
        $this->assertInstanceOf(ConfigField::class, $passwordField);
        $this->assertEquals('secret', $passwordField->getValue());
    }

    /**
     * Test setLogo method.
     */
    public function testSetLogo(): void
    {
        // Setup credential type mock to return a logo
        $this->credentialTypeMock->method('getLogo')
            ->willReturn('test-logo.png');

        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Add a field that should get a logo
        $configField = new ConfigField('test_field', 'Test Field', 'string', 'value');
        $credentialConfigFields->addField($configField);

        // Set logo
        $result = $credentialConfigFields->setLogo();

        // Test fluent interface
        $this->assertSame($credentialConfigFields, $result);

        // Test logo was set on field
        $field = $credentialConfigFields->getField('test_field');
        $this->assertEquals('test-logo.png', $field->getLogo());
    }

    /**
     * Test setLogo with no credential.
     */
    public function testSetLogoNoCredential(): void
    {
        $credentialConfigFields = new CredentialConfigFields();

        // Add a field
        $configField = new ConfigField('test_field', 'Test Field', 'string', 'value');
        $credentialConfigFields->addField($configField);

        // Set logo (should not fail but do nothing)
        $credentialConfigFields->setLogo();

        // Test logo was not set
        $field = $credentialConfigFields->getField('test_field');
        $this->assertEquals('', $field->getLogo());
    }

    /**
     * Test that inherited ConfigFields methods work properly.
     */
    public function testInheritedMethods(): void
    {
        $credentialConfigFields = new CredentialConfigFields($this->credentialMock);

        // Add fields
        $field1 = new ConfigField('field1', 'Field 1', 'string', 'value1');
        $field2 = new ConfigField('field2', 'Field 2', 'string', 'value2');

        $credentialConfigFields->addField($field1);
        $credentialConfigFields->addField($field2);

        // Test getFields method
        $fields = $credentialConfigFields->getFields();
        $this->assertCount(2, $fields);
        $this->assertArrayHasKey('field1', $fields);
        $this->assertArrayHasKey('field2', $fields);

        // Test getField method
        $this->assertSame($field1, $credentialConfigFields->getField('field1'));
        $this->assertSame($field2, $credentialConfigFields->getField('field2'));

        // Test getEnvArray method
        $envArray = $credentialConfigFields->getEnvArray();
        $this->assertEquals([
            'field1' => 'value1',
            'field2' => 'value2',
        ], $envArray);

        // Test getFieldNames method
        $fieldNames = $credentialConfigFields->getFieldNames();
        $this->assertEquals(['field1', 'field2'], $fieldNames);

        // Test Iterator implementation
        $fieldCount = 0;

        foreach ($credentialConfigFields as $key => $field) {
            ++$fieldCount;
            $this->assertTrue(\in_array($key, ['field1', 'field2'], true));
            $this->assertInstanceOf(ConfigField::class, $field);
        }

        $this->assertEquals(2, $fieldCount);
    }
}
