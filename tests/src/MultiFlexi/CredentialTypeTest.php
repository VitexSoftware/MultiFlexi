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

use MultiFlexi\Company;
use MultiFlexi\ConfigField;
use MultiFlexi\ConfigFields;
use MultiFlexi\CredentialType;
use MultiFlexi\credentialTypeInterface;
use MultiFlexi\CrTypeField;
use PHPUnit\Framework\TestCase;

/**
 * Tests for CredentialType class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.com>
 */
class CredentialTypeTest extends TestCase
{
    protected CredentialType $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        $this->object = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['dblink', 'loadFromSQL', 'listingQuery', 'addStatusMessage'])
            ->getMock();

        // Setup basic data for the credential type
        $this->object->setData([
            'id' => 1,
            'uuid' => 'test-uuid-123',
            'name' => 'Test Credential Type',
            'class' => 'Common',
            'company_id' => 1,
        ]);
    }

    /**
     * Test basic initialization.
     */
    public function testInitialization(): void
    {
        $credentialType = new CredentialType();
        $this->assertEquals('credential_type', $credentialType->getMyTable());
        $this->assertNotEmpty($credentialType->getDataValue('uuid'));
    }

    /**
     * Test takeData method with valid data.
     */
    public function testTakeDataWithValidData(): void
    {
        $data = [
            'id' => 2,
            'name' => 'Test Credential Type 2',
            'class' => 'Common',
        ];

        $result = $this->object->takeData($data);
        $this->assertGreaterThan(0, $result);
        $this->assertEquals($data['name'], $this->object->getDataValue('name'));
    }

    /**
     * Test takeData method with empty name.
     */
    public function testTakeDataWithEmptyName(): void
    {
        // Create a mock for Company class
        $companyMock = $this->createMock(Company::class);
        $companyMock->method('getRecordName')->willReturn('Test Company');

        // Create a partial mock for CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getHelper'])
            ->getMock();

        // Create a mock for the helper class
        $helperMock = $this->createMock(credentialTypeInterface::class);
        $helperMock->method('name')->willReturn('Common Helper');

        // Configure the credential type mock
        $credType->method('getHelper')->willReturn($helperMock);

        // Create reflection class to replace global new operator for Company
        $reflection = new \ReflectionClass(Company::class);
        $constructor = $reflection->getConstructor();
        $constructor->setAccessible(true);

        // Replace the global new operator with our mock
        $GLOBALS['mockCompany'] = $companyMock;
        $GLOBALS['origCompanyClass'] = Company::class;

        // Define a function to replace the Company constructor
        eval(<<<'EOD'
namespace MultiFlexi {
            function mockCompanyConstructor() {
                return $GLOBALS["mockCompany"];
            }

            class CompanyMocker extends \
EOD.$GLOBALS['origCompanyClass'].<<<'EOD'
 {
                public function __construct($id = null) {
                    return mockCompanyConstructor();
                }
            }
        }
EOD);

        // Backup the original class
        $origCompany = Company::class;

        try {
            // Run the test
            $data = [
                'name' => '',
                'class' => 'Common',
                'company_id' => 1,
            ];

            $result = $credType->takeData($data);
            $this->assertGreaterThan(0, $result);

            // With both class and company, the name should be "Common Helper / Test Company"
            $generatedName = $credType->getDataValue('name');
            $this->assertStringContainsString('Common Helper', $generatedName);
            $this->assertStringContainsString('Test Company', $generatedName);
        } finally {
            // Restore the original class
            unset($GLOBALS['mockCompany'], $GLOBALS['origCompanyClass']);
        }
    }

    /**
     * Test getHelper method.
     */
    public function testGetHelper(): void
    {
        // Create a partial mock for CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getDataValue', 'getMyKey'])
            ->getMock();

        // Configure the mock
        $credType->method('getDataValue')
            ->with($this->equalTo('class'))
            ->willReturn('Common');

        $credType->method('getMyKey')
            ->willReturn(1);

        // Get the helper
        $helper = $credType->getHelper();

        // Assert that helper is an instance of credentialTypeInterface
        $this->assertInstanceOf(credentialTypeInterface::class, $helper);

        // Assert that the helper is of the correct class
        $this->assertEquals('Common', \Ease\Functions::baseClassName($helper));
    }

    /**
     * Test columns method.
     */
    public function testColumns(): void
    {
        $columns = $this->object->columns();

        $this->assertIsArray($columns);
        $this->assertGreaterThan(0, \count($columns));

        // Check that required columns exist
        $columnNames = array_column($columns, 'name');
        $this->assertContains('id', $columnNames);
        $this->assertContains('name', $columnNames);
        $this->assertContains('uuid', $columnNames);
        $this->assertContains('logo', $columnNames);
        $this->assertContains('company_id', $columnNames);
    }

    /**
     * Test completeDataRow method.
     */
    public function testCompleteDataRow(): void
    {
        // Create a partial mock for the CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getHelper', 'getMyKey'])
            ->getMock();

        // Create a mock for the helper
        $helperMock = $this->createMock(credentialTypeInterface::class);
        $helperMock->method('name')->willReturn('Test Helper Name');
        $helperMock->method('logo')->willReturn('test-logo.png');

        // Configure the credential type mock
        $credType->method('getHelper')->willReturn($helperMock);
        $credType->method('getMyKey')->willReturn(1);

        // Set raw data for the test
        $rawData = [
            'id' => 1,
            'name' => '',
            'logo' => '',
            'company_id' => 1,
        ];

        // Process data row
        $completedRow = $credType->completeDataRow($rawData);

        // Check that the name and logo were set from the helper
        $this->assertStringContainsString('Test Helper Name', $completedRow['name']);
        $this->assertStringContainsString('test-logo.png', $completedRow['logo']);

        // Check that HTML elements were generated for logo and company_id
        $this->assertStringContainsString('<a', $completedRow['logo']);
        $this->assertStringContainsString('<img', $completedRow['logo']);
        $this->assertStringContainsString('<a', $completedRow['company_id']);
    }

    /**
     * Test getFields method.
     */
    public function testGetFields(): void
    {
        // Create a partial mock for CrTypeField
        $fieldMock = $this->getMockBuilder(CrTypeField::class)
            ->setMethods(['listingQuery'])
            ->getMock();

        // Create a mock for the query result
        $queryResult = [
            [
                'id' => 1,
                'keyname' => 'username',
                'type' => 'string',
                'description' => 'Username',
                'hint' => 'Enter your username',
                'defval' => 'defaultuser',
                'required' => 1,
                'helper' => 'username',
            ],
            [
                'id' => 2,
                'keyname' => 'password',
                'type' => 'password',
                'description' => 'Password',
                'hint' => 'Enter your password',
                'defval' => '',
                'required' => 1,
                'helper' => 'password',
            ],
        ];

        // Configure the field mock
        $fieldMock->method('listingQuery')->willReturnSelf();
        $fieldMock->method('where')->willReturn($queryResult);

        // Create a partial mock for CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getMyKey', 'getHelper'])
            ->getMock();

        // Create a mock for the helper
        $helperMock = $this->createMock(credentialTypeInterface::class);

        // Create mock fields
        $fieldsMock = new ConfigFields();
        $usernameField = new ConfigField('username', 'string', 'Username', 'Enter your username');
        $usernameField->setRequired(true);
        $passwordField = new ConfigField('password', 'password', 'Password', 'Enter your password');
        $passwordField->setRequired(true)->setSecret(true);
        $fieldsMock->addField($usernameField);
        $fieldsMock->addField($passwordField);

        // Configure the helper mock
        $helperMock->method('fieldsProvided')->willReturn($fieldsMock);

        // Configure the credential type mock
        $credType->method('getMyKey')->willReturn(1);
        $credType->method('getHelper')->willReturn($helperMock);

        // Replace the CrTypeField constructor
        $GLOBALS['mockCrTypeField'] = $fieldMock;

        // Define a function to create a mock instance
        eval(<<<'EOD'
namespace MultiFlexi {
            function createMockCrTypeField() {
                return $GLOBALS["mockCrTypeField"];
            }

            class CrTypeFieldMock extends CrTypeField {
                public function __construct() {
                    return createMockCrTypeField();
                }
            }
        }
EOD);

        // Backup the original class
        $origCrTypeField = CrTypeField::class;

        try {
            // Replace the class with our mock
            $reflectionProperty = new \ReflectionProperty('MultiFlexi\CrTypeField', 'class');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue(null, 'MultiFlexi\CrTypeFieldMock');

            // Call the method
            $fields = $credType->getFields();

            // Check the result
            $this->assertInstanceOf(ConfigFields::class, $fields);
            $this->assertEquals(2, $fields->count());

            // Check first field
            $usernameField = $fields->getFieldByCode('username');
            $this->assertNotNull($usernameField);
            $this->assertEquals('string', $usernameField->getType());
            $this->assertEquals('Enter your username', $usernameField->getHint());
            $this->assertTrue($usernameField->isRequired());

            // Check second field
            $passwordField = $fields->getFieldByCode('password');
            $this->assertNotNull($passwordField);
            $this->assertEquals('password', $passwordField->getType());
            $this->assertEquals('Enter your password', $passwordField->getHint());
            $this->assertTrue($passwordField->isRequired());
        } finally {
            // Restore the original class
            if (isset($reflectionProperty)) {
                $reflectionProperty->setValue(null, $origCrTypeField);
            }

            unset($GLOBALS['mockCrTypeField']);
        }
    }

    /**
     * Test query method.
     */
    public function testQuery(): void
    {
        // Create a partial mock for CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getFields', 'getHelper', 'getDataValue'])
            ->getMock();

        // Create mock fields
        $fieldsMock = new ConfigFields();
        $usernameField = new ConfigField('username', 'string', 'Username', 'Enter your username');
        $usernameField->setHelper('username');
        $passwordField = new ConfigField('password', 'password', 'Password', 'Enter your password');
        $passwordField->setHelper('password');
        $fieldsMock->addField($usernameField);
        $fieldsMock->addField($passwordField);

        // Create helper fields
        $helperFieldsMock = new ConfigFields();
        $helperUsernameField = new ConfigField('username', 'string', 'Username', 'Enter your username');
        $helperUsernameField->setValue('testuser')->setSource('config')->setNote('Username note');
        $helperPasswordField = new ConfigField('password', 'password', 'Password', 'Enter your password');
        $helperPasswordField->setValue('testpass')->setSource('config')->setNote('Password note');
        $helperFieldsMock->addField($helperUsernameField);
        $helperFieldsMock->addField($helperPasswordField);

        // Create a mock for the helper
        $helperMock = $this->createMock(credentialTypeInterface::class);
        $helperMock->method('query')->willReturn($helperFieldsMock);

        // Configure the credential type mock
        $credType->method('getFields')->willReturn($fieldsMock);
        $credType->method('getHelper')->willReturn($helperMock);
        $credType->method('getDataValue')->with($this->equalTo('class'))->willReturn('Common');

        // Call the method
        $queryFields = $credType->query();

        // Check the result
        $this->assertInstanceOf(ConfigFields::class, $queryFields);
        $this->assertEquals(2, $queryFields->count());

        // Check that the fields have values from the helper
        $usernameField = $queryFields->getFieldByCode('username');
        $this->assertEquals('testuser', $usernameField->getValue());
        $this->assertEquals('config', $usernameField->getSource());
        $this->assertEquals('Username note', $usernameField->getNote());

        $passwordField = $queryFields->getFieldByCode('password');
        $this->assertEquals('testpass', $passwordField->getValue());
        $this->assertEquals('config', $passwordField->getSource());
        $this->assertEquals('Password note', $passwordField->getNote());
    }

    /**
     * Test getLogo method.
     */
    public function testGetLogo(): void
    {
        $this->object->setDataValue('logo', 'test-logo.png');
        $logo = $this->object->getLogo();
        $this->assertEquals('test-logo.png', $logo);
    }

    /**
     * Test loadFromSQL method.
     */
    public function testLoadFromSQL(): void
    {
        // Create a partial mock for CredentialType
        $credType = $this->getMockBuilder(CredentialType::class)
            ->setMethods(['getHelper', 'getDataValue', 'setDataValue'])
            ->getMock();

        // Create a mock for the helper
        $helperMock = $this->createMock(credentialTypeInterface::class);
        $helperMock->method('logo')->willReturn('helper-logo.png');

        // Configure the credential type mock
        $credType->method('getHelper')->willReturn($helperMock);
        $credType->method('getDataValue')->willReturnMap([
            ['class', 'Common'],
            ['logo', ''],
        ]);

        // Expect setDataValue to be called with the helper's logo
        $credType->expects($this->once())
            ->method('setDataValue')
            ->with(
                $this->equalTo('logo'),
                $this->equalTo('helper-logo.png'),
            );

        // Use reflection to call protected parent::loadFromSQL
        $reflection = new \ReflectionClass(CredentialType::class);
        $method = $reflection->getMethod('loadFromSQL');
        $method->setAccessible(true);
        $method->invoke($credType);
    }
}
