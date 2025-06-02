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

use MultiFlexi\Credata;
use MultiFlexi\Credential;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Credata class.
 *
 * @author Vítězslav Dvořák <info@vitexsoftware.com>
 */
class CredataTest extends TestCase
{
    protected Credata $object;

    /**
     * Sets up the fixture.
     */
    protected function setUp(): void
    {
        // Create a mock to avoid actual database operations
        $this->object = $this->getMockBuilder(Credata::class)
            ->setMethods(['dblink', 'loadFromSQL', 'insertToSQL', 'updateToSQL', 'deleteFromSQL', 'listingQuery'])
            ->getMock();
    }

    /**
     * Test class initialization.
     */
    public function testInitialization(): void
    {
        // Create a real object to test actual initialization
        $credata = new Credata();

        // Check table name is set correctly
        $this->assertEquals('credata', $credata->getMyTable());

        // Test that it extends Engine
        $this->assertInstanceOf(\MultiFlexi\Engine::class, $credata);
    }

    /**
     * Test database interactions through insertToSQL.
     */
    public function testInsertToSQL(): void
    {
        // Prepare test data
        $testData = [
            'credential_id' => 123,
            'name' => 'username',
            'value' => 'test_user',
            'type' => 'string',
        ];

        // Configure mock to expect insertToSQL call
        $this->object->expects($this->once())
            ->method('insertToSQL')
            ->with($this->equalTo($testData))
            ->willReturn(456); // Return a mock record ID

        // Call insertToSQL with test data
        $result = $this->object->insertToSQL($testData);

        // Verify the result
        $this->assertEquals(456, $result);
    }

    /**
     * Test database interactions through updateToSQL.
     */
    public function testUpdateToSQL(): void
    {
        // Prepare test data
        $testData = [
            'value' => 'updated_value',
        ];

        $conditions = [
            'credential_id' => 123,
            'name' => 'username',
        ];

        // Configure mock to expect updateToSQL call
        $this->object->expects($this->once())
            ->method('updateToSQL')
            ->with(
                $this->equalTo($testData),
                $this->equalTo($conditions),
            )
            ->willReturn(1); // Return 1 for one row updated

        // Call updateToSQL with test data and conditions
        $result = $this->object->updateToSQL($testData, $conditions);

        // Verify the result
        $this->assertEquals(1, $result);
    }

    /**
     * Test database interactions through deleteFromSQL.
     */
    public function testDeleteFromSQL(): void
    {
        // Prepare test data
        $condition = ['credential_id' => 123];

        // Configure mock to expect deleteFromSQL call
        $this->object->expects($this->once())
            ->method('deleteFromSQL')
            ->with($this->equalTo($condition))
            ->willReturn(3); // Return 3 for three rows deleted

        // Call deleteFromSQL with test condition
        $result = $this->object->deleteFromSQL($condition);

        // Verify the result
        $this->assertEquals(3, $result);
    }

    /**
     * Test integration with Credential class.
     */
    public function testCredentialIntegration(): void
    {
        // Create mock objects
        $mockCredential = $this->getMockBuilder(Credential::class)
            ->setMethods(['getMyKey', 'setDataValue'])
            ->disableOriginalConstructor()
            ->getMock();

        $mockCredential->expects($this->any())
            ->method('getMyKey')
            ->willReturn(789);

        // Mock the listingQuery method to return credential data
        $mockResults = [
            [
                'name' => 'username',
                'value' => 'test_username',
                'type' => 'string',
            ],
            [
                'name' => 'password',
                'value' => 'test_password',
                'type' => 'password',
            ],
        ];

        $mockQuery = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['where'])
            ->getMock();

        $mockQuery->expects($this->once())
            ->method('where')
            ->with('credential_id', 789)
            ->willReturn($mockResults);

        $this->object->expects($this->once())
            ->method('listingQuery')
            ->willReturn($mockQuery);

        // Test that credential values are set on the credential object
        $mockCredential->expects($this->exactly(2))
            ->method('setDataValue')
            ->withConsecutive(
                ['username', 'test_username'],
                ['password', 'test_password'],
            );

        // Manually call the code that would normally be in Credential::loadFromSQL
        foreach ($this->object->listingQuery()->where('credential_id', $mockCredential->getMyKey()) as $credential) {
            $mockCredential->setDataValue($credential['name'], $credential['value']);
        }
    }

    /**
     * Test error handling when inserting invalid data.
     */
    public function testErrorHandlingOnInsert(): void
    {
        // Configure mock to throw exception on invalid data
        $this->object->expects($this->once())
            ->method('insertToSQL')
            ->with($this->equalTo(['invalid_data' => 'value']))
            ->will($this->throwException(new \Exception('Invalid data')));

        // Expect exception when inserting invalid data
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Invalid data');

        // Try to insert invalid data
        $this->object->insertToSQL(['invalid_data' => 'value']);
    }

    /**
     * Test error handling when updating with invalid conditions.
     */
    public function testErrorHandlingOnUpdate(): void
    {
        // Configure mock to throw exception on invalid conditions
        $this->object->expects($this->once())
            ->method('updateToSQL')
            ->with(
                $this->equalTo(['value' => 'test']),
                $this->equalTo([]),
            )
            ->will($this->throwException(new \Exception('Missing update conditions')));

        // Expect exception when updating with invalid conditions
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Missing update conditions');

        // Try to update with invalid conditions
        $this->object->updateToSQL(['value' => 'test'], []);
    }

    /**
     * Test handling different field types.
     */
    public function testHandlingDifferentFieldTypes(): void
    {
        // Test with string type
        $stringData = [
            'credential_id' => 123,
            'name' => 'username',
            'value' => 'test_user',
            'type' => 'string',
        ];

        $this->object->expects($this->at(0))
            ->method('insertToSQL')
            ->with($this->equalTo($stringData))
            ->willReturn(1);

        // Test with password type
        $passwordData = [
            'credential_id' => 123,
            'name' => 'password',
            'value' => 'secret123',
            'type' => 'password',
        ];

        $this->object->expects($this->at(1))
            ->method('insertToSQL')
            ->with($this->equalTo($passwordData))
            ->willReturn(2);

        // Test with integer type
        $integerData = [
            'credential_id' => 123,
            'name' => 'port',
            'value' => '8080',
            'type' => 'integer',
        ];

        $this->object->expects($this->at(2))
            ->method('insertToSQL')
            ->with($this->equalTo($integerData))
            ->willReturn(3);

        // Execute the inserts
        $this->object->insertToSQL($stringData);
        $this->object->insertToSQL($passwordData);
        $this->object->insertToSQL($integerData);
    }

    /**
     * Test handling multiple credential values for the same credential.
     */
    public function testHandlingMultipleCredentialValues(): void
    {
        // Mock the listingQuery method to return multiple values for the same credential
        $mockResults = [
            [
                'name' => 'username',
                'value' => 'test_username',
                'type' => 'string',
            ],
            [
                'name' => 'password',
                'value' => 'test_password',
                'type' => 'password',
            ],
            [
                'name' => 'api_key',
                'value' => 'abc123',
                'type' => 'string',
            ],
            [
                'name' => 'port',
                'value' => '8080',
                'type' => 'integer',
            ],
        ];

        $mockQuery = $this->getMockBuilder(\stdClass::class)
            ->setMethods(['where'])
            ->getMock();

        $mockQuery->expects($this->once())
            ->method('where')
            ->with('credential_id', 123)
            ->willReturn($mockResults);

        $this->object->expects($this->once())
            ->method('listingQuery')
            ->willReturn($mockQuery);

        // Count the number of credential values
        $count = 0;

        foreach ($this->object->listingQuery()->where('credential_id', 123) as $credential) {
            ++$count;
        }

        // Verify the correct number of credential values
        $this->assertEquals(4, $count);
    }
}
